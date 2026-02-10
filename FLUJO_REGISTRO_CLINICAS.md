# Flujo de Registro de Clínicas - Análisis Completo

## 📋 Resumen Ejecutivo

El sistema maneja **dos flujos de registro separados**:

1. **Registro de Usuario Individual** (`/api/registro`) - Crea usuario SIN clínica
2. **Registro de Clínica Completa** (`/api/clinicas`) - Crea clínica + sucursal + administrador

---

## 🔐 1. Registro de Usuario Individual

### Endpoint
```
POST /api/registro
```

### Controlador
`app/Http/Controllers/Api/AuthController.php` → método `signup()`

### Campos Requeridos
```php
SignupRequest validación:
- nombre: required|string
- apellidoPat: required|string
- apellidoMat: required|string
- cedula: nullable|unique:users,cedula
- email: required|email|unique:users,email
- password: required|confirmed (min 8, letras, números, símbolos)
- isAdmin: string
- rol: nullable|string|in:doctor,doctora,licenciado,recepcionista,etc.
```

### Proceso
1. **Validación** de datos con `SignupRequest`
2. **Generación** de token de verificación email (60 caracteres aleatorios)
3. **Creación** del usuario:
   ```php
   User::create([
       'nombre' => $validated['nombre'],
       'apellidoPat' => $validated['apellidoPat'],
       'apellidoMat' => $validated['apellidoMat'],
       'email' => $validated['email'],
       'cedula' => $validated['cedula'],
       'password' => Hash::make($validated['password']),
       'rol' => $validated['rol'] ?? null,
       'isAdmin' => filter_var($validated['isAdmin'] ?? false, FILTER_VALIDATE_BOOLEAN),
       'imagen' => 'perfiles/avatar-default.png',
       'email_verification_token' => $verificationToken,
       'email_verified' => false
   ]);
   ```
4. **Envío** de email de verificación
5. **Generación** de API token Sanctum
6. **Retorno** inmediato: `{ token, user }`

### ⚠️ Problema Identificado
- **NO se asigna `clinica_id` ni `sucursal_id`** durante este registro
- Usuario queda **"huérfano"** sin clínica asociada
- Recibe token y puede acceder al sistema inmediatamente
- Email verification **NO bloquea el acceso** (token se entrega antes de verificar)

### Frontend
`src/views/auth/Signup.jsx` → usa hook `useAuth()` → método `registro()`

```javascript
const registro = (datos, setErrores) => {
    clienteAxios.post('/api/registro', datos)
    .then(({data}) => {
        setUser(data.user)
        setToken(data.token);
    }).catch(err => {
        const response = err.response;
        setErrores(response.data.errors)
    })
}
```

---

## 🏥 2. Registro de Clínica Completa (Flujo Principal)

### Endpoint
```
POST /api/clinicas
```

### Controlador
`app/Http/Controllers/ClinicaController.php` → método `store()`

### Campos Requeridos

#### Datos de la Clínica
```php
- nombre: required|string|max:255
- tipo_clinica: nullable|string|in:[tipos de config]
- modulos_habilitados: nullable|array
- email: required|email|unique:clinicas,email
- telefono: nullable|string|max:20
- direccion: nullable|string|max:500
- logo: nullable|image|mimes:jpeg,png,jpg|max:2048
```

#### Datos del Plan de Pago
```php
- plan: required|in:profesional,clinica,empresarial
- duration: required|in:mensual,anual
- precio_final: required|numeric|min:0
- cupon: nullable|string|max:50
- payment_method: required|string|max:50
- transaction_id: required|string|max:100
```

#### Datos del Administrador
```php
- admin_nombre: required|string|max:255
- admin_apellidoPat: required|string|max:255
- admin_apellidoMat: required|string|max:255
- admin_email: required|email|unique:users,email
- admin_password: required|string|min:8
- admin_cedula: nullable|string|max:20
- admin_rol: nullable|string|in:[roles config]
```

### Proceso Completo

#### Paso 1: Determinar Límites del Plan
```php
private function getPlanLimits($plan) {
    $limits = [
        'profesional' => [
            'sucursales' => 1,
            'usuarios' => 3,
            'pacientes' => 200,
            'ia_mensual' => 500
        ],
        'clinica' => [
            'sucursales' => 5,
            'usuarios' => 15,
            'pacientes' => 1000,
            'ia_mensual' => 2500
        ],
        'empresarial' => [
            'sucursales' => 999,      // Ilimitado
            'usuarios' => 999,         // Ilimitado
            'pacientes' => 999999,     // Ilimitado
            'ia_mensual' => 999999     // Ilimitado
        ]
    ];
    return $limits[$plan] ?? $limits['profesional'];
}
```

#### Paso 2: Crear Clínica
```php
$clinica = Clinica::create([
    'nombre' => $request->nombre,
    'tipo_clinica' => $request->tipo_clinica ?? 'rehabilitacion_cardiopulmonar',
    'modulos_habilitados' => $request->modulos_habilitados ?? [],
    'email' => $request->email,
    'telefono' => $request->telefono,
    'direccion' => $request->direccion,
    'plan' => $request->plan,
    'duration' => $request->duration,
    'pagado' => true,  // ✅ Pago ya procesado
    'fecha_vencimiento' => $this->calculateExpirationDate($request->duration),
    'activa' => true,  // ✅ Activa inmediatamente
    'permite_multiples_sucursales' => $limites['sucursales'] > 1,
    'max_sucursales' => $limites['sucursales'],
    'max_usuarios' => $limites['usuarios'],
    'max_pacientes' => $limites['pacientes'],
]);
```

#### Paso 3: Crear Sucursal Principal Automática
```php
$sucursal = $clinica->sucursales()->create([
    'nombre' => $request->nombre . ' - Principal',
    'codigo' => 'SUC-' . str_pad($clinica->id, 3, '0', STR_PAD_LEFT) . '-001',
    'direccion' => $request->direccion,
    'telefono' => $request->telefono,
    'es_principal' => true,
    'activa' => true,
]);
```

#### Paso 4: Crear Usuario Administrador (Super Admin)
```php
$admin = User::create([
    'nombre' => $request->admin_nombre,
    'apellidoPat' => $request->admin_apellidoPat,
    'apellidoMat' => $request->admin_apellidoMat,
    'email' => $request->admin_email,
    'password' => Hash::make($request->admin_password),
    'cedula' => $request->admin_cedula,
    'rol' => $request->admin_rol ?: null,
    'isAdmin' => true,
    'isSuperAdmin' => true,  // 🔑 Super admin de la clínica
    'clinica_id' => $clinica->id,  // ✅ AQUÍ se asigna clinica_id
    'sucursal_id' => $sucursal->id,  // ✅ AQUÍ se asigna sucursal_id
    'email_verified' => true,  // ✅ Email pre-verificado
]);
```

#### Paso 5: Log de Transacción
```php
\Log::info('Clínica registrada con pago exitoso', [
    'clinica_id' => $clinica->id,
    'sucursal_id' => $sucursal->id,
    'plan' => $request->plan,
    'duration' => $request->duration,
    'precio_final' => $request->precio_final,
    'cupon' => $request->cupon,
    'payment_method' => $request->payment_method,
    'transaction_id' => $request->transaction_id,
    'admin_email' => $request->admin_email,
    'limites' => $limites
]);
```

#### Paso 6: Respuesta
```php
return response()->json([
    'success' => true,
    'message' => 'Clínica registrada exitosamente. Puedes iniciar sesión con el email del administrador.',
    'clinica' => $clinica->load('users', 'sucursales'),
    'admin' => $admin,
    'sucursal' => $sucursal
], 201);
```

---

## 💳 3. Planes de Pago y Configuración

### Planes Disponibles

#### 🟢 Plan Profesional - $1,499/mes
```
✓ 1 Sucursal
✓ Hasta 3 usuarios
✓ Hasta 200 pacientes
✓ 500 análisis IA/mes
✓ Transcripción de voz
✓ Expedientes ilimitados
✓ Reportes básicos
✓ Soporte por email
✓ Backup automático
```

#### 🔵 Plan Clínica - $3,999/mes ⭐ MÁS POPULAR
```
✓ Hasta 5 Sucursales
✓ Hasta 15 usuarios
✓ Hasta 1,000 pacientes
✓ 2,500 análisis IA/mes
✓ Transcripción de voz
✓ Expedientes ilimitados
✓ Reportes avanzados
✓ Soporte prioritario
✓ Backup automático
✓ Dashboard por sucursal
```

#### 🟣 Plan Empresarial - $8,999/mes
```
✓ Sucursales ilimitadas
✓ Usuarios ilimitados
✓ Pacientes ilimitados
✓ IA ilimitada
✓ Transcripción de voz
✓ Expedientes ilimitados
✓ Reportes personalizados
✓ Soporte 24/7
✓ Backup en tiempo real
✓ API personalizada
✓ Capacitación incluida
✓ Gestor de cuenta dedicado
```

### Descuentos

#### Descuento por Pago Anual
- **10% de descuento** automático al elegir duración anual
- Ejemplo Plan Clínica: $3,999 × 12 = $47,988 → Con descuento: **$43,189/año**

#### Sistema de Cupones
Cupones configurados en frontend (`PlanSelectionModal.jsx`):

```javascript
const coupons = {
    'WELCOME20': { discount: 20, type: 'percentage', valid: true },  // 20% descuento
    'CLINIC50': { discount: 50, type: 'fixed', valid: true },        // $50 descuento
    'FIRST30': { discount: 30, type: 'percentage', valid: true },    // 30% descuento
    'ANNUAL100': { discount: 100, type: 'fixed', valid: true }       // $100 descuento
};
```

### Fecha de Vencimiento

```php
private function calculateExpirationDate($duration) {
    $now = now();
    
    if ($duration === 'anual') {
        return $now->addYear();  // +1 año
    } else {
        return $now->addMonth(); // +1 mes
    }
}
```

---

## 🔄 4. Frontend: Flujo de Registro de Clínica

### Componentes Involucrados

#### 1. PlanSelectionModal.jsx
**Ubicación:** `src/components/PlanSelectionModal.jsx`

**Funcionalidad:**
- Muestra los 3 planes disponibles
- Selector de duración (mensual/anual)
- Validación y aplicación de cupones
- Cálculo de precio final con descuentos
- Selección de plan

**Props:**
```javascript
{
    isOpen: boolean,
    onClose: function,
    onSelectPlan: function,
    clinicData: object
}
```

**Datos retornados al seleccionar plan:**
```javascript
{
    ...plans[selectedPlan],
    duration: 'mensual' | 'anual',
    finalPrice: number,
    coupon: object | null,
    couponCode: string
}
```

#### 2. PaymentProcessing.jsx
**Ubicación:** `src/components/PaymentProcessing.jsx`

**Funcionalidad:**
- Formulario de pago con tarjeta
- Validación de datos de tarjeta
- Simulación de procesamiento de pago
- Estados: form, processing, success, error

**Props:**
```javascript
{
    clinicData: object,
    planData: object,
    onPaymentSuccess: function,
    onPaymentError: function
}
```

**Datos de tarjeta capturados:**
```javascript
{
    number: string,     // Formato: "1234 5678 9012 3456"
    expiry: string,     // Formato: "MM/YY"
    cvv: string,        // 3-4 dígitos
    name: string        // Nombre en tarjeta
}
```

**Simulación de pago:**
```javascript
const simulatePayment = async () => {
    setIsProcessing(true);
    setPaymentStep('processing');
    
    // Simular procesamiento (3 segundos)
    setTimeout(() => {
        const success = Math.random() > 0.1; // 90% probabilidad éxito
        
        if (success) {
            setPaymentStep('success');
            onPaymentSuccess({
                clinicData,
                planData,
                paymentData: {
                    method: 'card',
                    amount: planData.finalPrice,
                    transactionId: 'TXN_' + Date.now()
                }
            });
        } else {
            setPaymentStep('error');
            onPaymentError('Error en procesamiento...');
        }
    }, 3000);
};
```

---

## 🔒 5. Multitenancy: Aislamiento por Clínica

### Modelo User

**Campos relevantes:**
```php
protected $fillable = [
    'nombre',
    'apellidoPat',
    'apellidoMat',
    'cedula',
    'email',
    'password',
    'isAdmin',
    'isSuperAdmin',
    'imagen',
    'firma_digital',
    'email_verification_token',
    'email_verified',
    'clinica_id',      // 🔑 ID de la clínica
    'sucursal_id',     // 🔑 ID de la sucursal
    'rol'
];
```

**Relaciones:**
```php
public function clinica() {
    return $this->belongsTo(Clinica::class);
}

public function sucursal() {
    return $this->belongsTo(Sucursal::class);
}
```

### Middleware: multi.tenant

**Ubicación:** Aplicado en rutas protegidas

**Funcionamiento:**
```php
Route::middleware(['auth:sanctum', 'multi.tenant'])->group(function () {
    // Todas estas rutas filtran por clinica_id del usuario autenticado
    Route::get('/api/pacientes', ...);
    Route::get('/api/finanzas/corte-caja', ...);
    // etc.
});
```

**Lógica del middleware:**
1. Obtiene `clinica_id` y `sucursal_id` del usuario autenticado
2. Filtra automáticamente todas las consultas por estos IDs
3. Previene acceso a datos de otras clínicas
4. Asegura que nuevos registros hereden estos IDs

### Asignación de clinica_id

#### ✅ Flujo Correcto (Registro de Clínica Completa)
```
1. Usuario llena formulario de registro de clínica
2. Selecciona plan y procesa pago
3. Backend crea: Clinica → Sucursal → User (con clinica_id y sucursal_id)
4. Usuario puede acceder inmediatamente
```

#### ⚠️ Flujo Problemático (Registro Individual)
```
1. Usuario llena formulario simple (/registro)
2. Backend crea User sin clinica_id ni sucursal_id
3. Usuario recibe token pero NO puede crear pacientes ni usar funcionalidades
4. ¿Cómo se asigna posteriormente la clínica? 🤔
```

**Opciones para usuarios sin clínica:**
- Asignación manual por superadmin del sistema
- Crear su propia clínica posteriormente
- Ser invitado a una clínica existente (funcionalidad no implementada)

---

## ✅ 6. Activación y Acceso Inmediato

### Respuestas a Preguntas Clave

#### ¿Requiere activación manual?
**NO** ❌

- Registro de clínica completa → **Acceso inmediato**
- Campo `activa` se establece en `true` automáticamente
- Campo `pagado` se establece en `true` después de pago
- NO hay proceso de aprobación manual por administradores del sistema

#### ¿Se requiere verificación de email?
**NO BLOQUEANTE** ⚠️

**Registro Individual:**
- Se envía email de verificación
- `email_verified` = false inicialmente
- Usuario **recibe token API inmediatamente**
- Puede acceder sin verificar email

**Registro de Clínica Completa:**
- Admin se crea con `email_verified` = true
- **NO se envía email de verificación**
- Acceso inmediato garantizado

#### ¿Cuándo se activa la clínica?
**INMEDIATAMENTE** ✅

```php
$clinica = Clinica::create([
    // ... otros campos
    'pagado' => true,           // ✅ Ya procesado el pago
    'activa' => true,           // ✅ Activa desde el inicio
    'fecha_vencimiento' => ..., // 1 mes o 1 año desde hoy
]);
```

#### ¿Cómo se maneja la expiración?
**Sistema de Vencimiento por Fechas**

**Modelo Clinica - Métodos de verificación:**
```php
// Verificar si está activa y no expirada
public function isActive() {
    return $this->activa && 
           $this->pagado && 
           (!$this->fecha_vencimiento || $this->fecha_vencimiento->isFuture());
}

// Verificar si expiró
public function isExpired() {
    return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
}
```

**Endpoint de verificación:**
```
GET /api/clinicas/{id}/check-subscription

Response:
{
    "clinica": {...},
    "is_active": true,
    "is_expired": false,
    "days_until_expiry": 28
}
```

---

## 🔄 7. Renovación de Suscripción

### Endpoint
```
POST /api/clinicas/{id}/renew
```

### Campos
```php
- plan: required|in:mensual,trimestral,anual
- pagado: boolean (default: true)
```

### Proceso
```php
$clinica->update([
    'plan' => $request->plan,
    'pagado' => true,
    'fecha_vencimiento' => $this->calculateExpirationDate($request->plan),
    'activa' => true  // Reactivar si estaba desactivada
]);
```

---

## 📊 8. Gestión de Clínica (Post-Registro)

### Vista Frontend
**Ubicación:** `src/views/Clinica.jsx`

**Acceso:** Solo SuperAdmin (`isSuperAdmin: true`)

**Funcionalidades:**

#### Tab 1: Información de la Clínica
- Editar datos: nombre, email, teléfono, dirección
- Cambiar plan (solo superAdmin)
- Subir/cambiar logo
- Ver estado de suscripción
- Ver fecha de vencimiento

#### Tab 2: Gestión de Sucursales
- Listar sucursales
- Crear nueva sucursal (si plan lo permite)
- Editar sucursal existente
- Activar/desactivar sucursales
- Ver código de sucursal

**Endpoints utilizados:**
```
GET  /api/clinica/current        - Obtener clínica del usuario
PUT  /api/clinica/update         - Actualizar datos
POST /api/clinica/upload-logo    - Subir logo
GET  /api/clinicas/{id}          - Ver detalles
```

---

## 🚨 9. Problemas y Consideraciones

### ⚠️ Problema 1: Usuarios Huérfanos
**Descripción:** Registro individual (`/api/registro`) crea usuarios sin `clinica_id`

**Impacto:**
- Usuario puede iniciar sesión
- NO puede crear pacientes (falla middleware multi.tenant)
- NO puede usar funcionalidades principales del sistema

**Soluciones posibles:**
1. **Deshabilitar registro individual** - Forzar solo registro completo
2. **Onboarding guiado** - Después de registro, obligar a crear/unirse a clínica
3. **Sistema de invitaciones** - Permitir que clínicas inviten usuarios
4. **Asignación manual** - Superadmin del sistema asigna clínicas

### ⚠️ Problema 2: Verificación de Email No Bloqueante
**Descripción:** Usuarios reciben token antes de verificar email

**Riesgos:**
- Emails falsos pueden registrarse
- No hay garantía de comunicación válida
- Posible abuso del sistema

**Soluciones posibles:**
1. **Bloquear acceso hasta verificación**
2. **Funcionalidad limitada** hasta verificar
3. **Re-envío automático** de verificación
4. **Expiración** de cuentas no verificadas

### ⚠️ Problema 3: Pago Simulado en Frontend
**Descripción:** PaymentProcessing.jsx simula pagos, no procesa reales

**Código actual:**
```javascript
const success = Math.random() > 0.1; // Simula 90% éxito
```

**Riesgos:**
- Clínicas creadas sin pago real
- No hay integración con pasarela de pago
- No hay validación de transacciones

**Soluciones posibles:**
1. **Integrar Stripe/PayPal** - Procesamiento real
2. **Webhook de confirmación** - Esperar confirmación antes de activar
3. **Período de prueba** - X días gratis, luego requerir pago
4. **Pago manual** - Validación por administrador

### ⚠️ Problema 4: Cupones Solo en Frontend
**Descripción:** Validación de cupones solo en JavaScript, no en backend

**Riesgos:**
- Fácil manipulación desde DevTools
- No hay registro de uso de cupones
- Posible abuso de descuentos

**Soluciones posibles:**
1. **Validación en backend** - Tabla `cupones` en BD
2. **Límite de usos** - Cantidad máxima por cupón
3. **Auditoría** - Registrar cada aplicación de cupón
4. **Expiración** - Fechas de validez

### ⚠️ Problema 5: Sin Control de Límites
**Descripción:** Límites de plan (usuarios, pacientes, sucursales) se guardan pero no se validan

**Impacto:**
- Plan Profesional podría tener >200 pacientes
- Plan Profesional podría tener >3 usuarios
- No hay enforcement de los límites pagados

**Soluciones posibles:**
1. **Middleware de validación** - Verificar antes de crear
2. **Soft limits** - Avisar cuando se acerca al límite
3. **Hard limits** - Bloquear creación cuando se alcanza
4. **Upgrade automático** - Sugerir cambio de plan

---

## 📝 10. Recomendaciones

### Prioridad Alta 🔴

1. **Implementar procesamiento de pago real**
   - Integrar Stripe o PayPal
   - Validar transacciones en backend
   - Webhook de confirmación

2. **Validar límites de plan**
   - Middleware para verificar antes de crear
   - Mensajes claros cuando se alcanza límite
   - Sugerir upgrade de plan

3. **Eliminar o rediseñar registro individual**
   - Opción 1: Deshabilitar `/api/registro` completamente
   - Opción 2: Agregar onboarding para crear/unirse a clínica

4. **Validación de cupones en backend**
   - Migración para tabla `cupones`
   - Endpoints de validación
   - Registro de uso

### Prioridad Media 🟡

5. **Bloquear acceso hasta verificación de email**
   - Middleware para rutas críticas
   - Frontend muestra mensaje de verificación

6. **Sistema de invitaciones**
   - Clínicas pueden invitar usuarios
   - Token de invitación con expiración
   - Usuario acepta y se asigna automáticamente

7. **Dashboard de uso de recursos**
   - Mostrar uso actual vs límites
   - Gráficas de consumo
   - Alertas cuando se acerca a límite

### Prioridad Baja 🟢

8. **Período de prueba gratuito**
   - 14 días gratis para probar
   - Requerir pago después
   - Notificaciones de expiración

9. **Panel de administración global**
   - Ver todas las clínicas
   - Activar/desactivar manualmente
   - Asignar usuarios huérfanos

10. **Métricas y analytics**
    - Conversión de registro a pago
    - Planes más populares
    - Tasa de renovación

---

## 🔍 11. Endpoints Completos

### Públicos (Sin Autenticación)

```
POST /api/registro              - Registro de usuario individual
POST /api/login                 - Inicio de sesión
POST /api/forgot-password       - Solicitar reset de contraseña
POST /api/reset-password/{token} - Restablecer contraseña
GET  /api/verify-email/{token}  - Verificar email
GET  /api/clinicas/tipos        - Obtener tipos de clínicas
POST /api/clinicas              - Registro de clínica completa
```

### Protegidos (auth:sanctum + multi.tenant)

```
POST /api/logout                     - Cerrar sesión
GET  /api/clinica/current            - Obtener clínica del usuario
PUT  /api/clinica/update             - Actualizar clínica
POST /api/clinica/upload-logo        - Subir logo
GET  /api/clinicas/{id}              - Ver detalles de clínica
PUT  /api/clinicas/{id}              - Actualizar clínica (admin)
POST /api/clinicas/{id}/renew        - Renovar suscripción
GET  /api/clinicas/{id}/check-subscription - Verificar estado
```

---

## 📅 Fecha de Análisis
**6 de febrero de 2026**

---

## 🎯 Conclusión

El sistema tiene **dos flujos distintos de registro**:

1. **Registro Individual** - Crea usuario sin clínica (problemático)
2. **Registro de Clínica Completa** - Flujo completo y funcional (recomendado)

**Flujo recomendado:**
- Usuario visita sitio web
- Selecciona plan y duración
- Llena datos de clínica y administrador
- Procesa pago
- **Acceso inmediato** sin aprobación manual
- Email pre-verificado para admin

**Estado actual:**
- ✅ Activación inmediata sin aprobación manual
- ✅ Sistema de planes con límites definidos
- ✅ Multitenancy robusto con aislamiento por clínica
- ✅ Sucursal principal creada automáticamente
- ⚠️ Pago simulado (no real)
- ⚠️ Cupones no validados en backend
- ⚠️ Límites no enforceados
- ⚠️ Registro individual crea usuarios huérfanos

**Próximos pasos críticos:**
1. Implementar procesamiento de pago real
2. Validar límites de plan antes de crear recursos
3. Decidir qué hacer con registro individual
4. Mover validación de cupones a backend
