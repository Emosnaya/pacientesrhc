# Sistema de Egresos de Caja - Documentación

## Descripción General
Sistema completo para registrar retiros de caja, gastos operacionales, compras y otros egresos. Se integra con el sistema de pagos existente para ofrecer un control completo del flujo de efectivo (ingresos - egresos = balance).

## Características Principales

### 1. Tipos de Egresos Soportados
El sistema maneja 7 categorías de egresos:

- **Compra de Material**: Insumos, materiales médicos/dentales
- **Servicio**: Contratación de servicios externos
- **Mantenimiento**: Reparaciones y mantenimiento de equipo
- **Nómina**: Pagos de nómina y sueldos
- **Renta**: Pago de arrendamiento de locales
- **Servicios Públicos**: Luz, agua, internet, teléfono
- **Otro**: Otros gastos no categorizados

### 2. Seguridad y Encriptación
Todos los datos financieros están encriptados:
- Monto
- Concepto
- Proveedor/Beneficiario
- Número de factura
- Notas adicionales

### 3. Multi-tenancy
- Aislamiento por `clinica_id` y `sucursal_id`
- Cada sucursal maneja sus propios egresos
- Auditoría automática con `user_id` del registrador

## Backend (Laravel)

### Migración
**Archivo**: `database/migrations/2026_02_14_020000_create_egresos_table.php`

```sql
CREATE TABLE egresos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    clinica_id BIGINT NOT NULL,
    sucursal_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    monto TEXT NOT NULL COMMENT 'Encrypted',
    tipo_egreso ENUM(...) NOT NULL,
    concepto TEXT NOT NULL COMMENT 'Encrypted',
    proveedor TEXT NULL COMMENT 'Encrypted',
    factura TEXT NULL COMMENT 'Encrypted',
    notas TEXT NULL COMMENT 'Encrypted',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Índices optimizados**:
- `[clinica_id, created_at]`
- `[sucursal_id, created_at]`
- `[user_id, created_at]`
- `tipo_egreso`

### Modelo Egreso
**Archivo**: `app/Models/Egreso.php`

**Relaciones**:
- `clinica()` - BelongsTo
- `sucursal()` - BelongsTo
- `usuario()` - BelongsTo

**Scopes útiles**:
```php
Egreso::byDate('2026-02-14')->get();
Egreso::betweenDates('2026-02-01', '2026-02-28')->get();
Egreso::byTipo('compra_material')->get();
Egreso::hoy()->get();
Egreso::mesActual()->get();
```

**Accessors**:
- `monto_formateado`: Retorna `$1,500.00 MXN`
- `tipo_egreso_label`: Retorna etiqueta legible ("Compra de Material")

### Controlador
**Archivo**: `app/Http/Controllers/FinanzasController.php`

#### Endpoints Disponibles

**POST /api/finanzas/egresos**
Registrar un nuevo egreso.

Validaciones:
- `monto`: required, numeric, min:0.01
- `tipo_egreso`: required, in:[compra_material,servicio,mantenimiento,nomina,renta,servicios_publicos,otro]
- `concepto`: required, string, max:500
- `proveedor`: nullable, string
- `factura`: nullable, string
- `notas`: nullable, string

Response (201):
```json
{
    "message": "Egreso registrado exitosamente",
    "egreso": {
        "id": 1,
        "monto": 1500.00,
        "tipo_egreso": "compra_material",
        "concepto": "Compra de guantes desechables",
        "proveedor": "Dental Supply SA",
        "factura": "FAC-2026-001",
        "usuario": {...},
        "created_at": "2026-02-14T10:30:00"
    }
}
```

**GET /api/finanzas/egresos**
Listar egresos con filtros.

Query params:
- `fecha`: Filtrar por fecha específica (YYYY-MM-DD)
- `fecha_inicio` y `fecha_fin`: Rango de fechas
- `tipo_egreso`: Filtrar por tipo
- `per_page`: Paginación (default: 50)

Response (200):
```json
{
    "data": [...],
    "current_page": 1,
    "per_page": 50,
    "total": 150
}
```

**GET /api/finanzas/egresos/{id}**
Ver detalle de un egreso específico.

**DELETE /api/finanzas/egresos/{id}**
Eliminar egreso (solo admin/superadmin).

#### Corte de Caja Actualizado

**GET /api/finanzas/corte-caja**
Ahora incluye información de egresos y balance.

Response actualizada:
```json
{
    "fecha": "2026-02-14",
    "totales_por_metodo": [
        { "metodo_pago": "efectivo", "total": 15000.00, "cantidad": 30 },
        { "metodo_pago": "tarjeta", "total": 8500.00, "cantidad": 15 }
    ],
    "total_ingresos": 23500.00,
    "total_egresos": 3500.00,
    "balance": 20000.00,
    "cantidad_pagos": 45,
    "cantidad_egresos": 8,
    "ultimos_pagos": [...],
    "ultimos_egresos": [
        {
            "id": 8,
            "monto": 850.00,
            "tipo_egreso": "servicios_publicos",
            "concepto": "Pago de luz febrero",
            "proveedor": "CFE",
            "usuario": {...},
            "created_at": "2026-02-14T15:30:00"
        }
    ]
}
```

**Cálculo del balance**:
```
Balance = Total Ingresos - Total Egresos
```

## Frontend (React)

### Componente ModalRegistrarEgreso
**Archivo**: `src/components/ModalRegistrarEgreso.jsx`

**Props**:
- `isOpen` (boolean): Control de visibilidad
- `onClose` (function): Callback al cerrar
- `onSuccess` (function): Callback al registrar exitosamente

**Campos del formulario**:
1. **Monto** (requerido): Input numérico con prefijo `$`
2. **Tipo de Egreso** (requerido): Select con 7 opciones
3. **Concepto** (requerido): Textarea con límite de 500 caracteres
4. **Proveedor** (opcional): Input de texto
5. **Factura/Comprobante** (opcional): Input de texto
6. **Notas Adicionales** (opcional): Textarea

**Validaciones frontend**:
- Monto debe ser > 0
- Tipo de egreso obligatorio
- Concepto no puede estar vacío

**Estilos**:
- Tema rojo (diferenciando de los pagos en verde/purple)
- Icono `FaMoneyBillWave`
- Modal responsivo con react-responsive-modal

### Vista Caja Actualizada
**Archivo**: `src/views/Caja.jsx`

#### Cambios en las tarjetas de resumen

**Antes** (3 cards):
- Total del día
- Efectivo
- Tarjeta/Transferencia

**Ahora** (4 cards):
1. **Ingresos del día** (verde): Total de pagos recibidos
2. **Egresos del día** (rojo): Total de gastos/retiros
3. **Balance** (azul/rojo): Ingresos - Egresos
4. **Efectivo** (verde): Efectivo recibido

El card de **Balance** cambia de color:
- Azul si balance ≥ 0
- Rojo si balance < 0

#### Nueva tabla de egresos
Sección adicional debajo de "Últimos pagos registrados":

**Columnas**:
- Hora
- Tipo (con badge de color según categoría)
- Concepto
- Proveedor
- Monto (en rojo con signo negativo)
- Registró

**Color coding por tipo**:
- Compra Material: Azul
- Servicio: Morado
- Mantenimiento: Amarillo
- Nómina: Verde
- Renta: Rojo
- Servicios Públicos: Índigo
- Otro: Gris

#### Nuevo botón "Registrar Egreso"
- Color: Rojo (`bg-red-600`)
- Icono: `FaMinusCircle`
- Ubicación: Entre "Cortes y Reportes" y "Pago sin Paciente"

## Casos de Uso

### Caso 1: Compra de material médico
```
1. Click en "Registrar Egreso"
2. Monto: $1,500.00
3. Tipo: "Compra de Material"
4. Concepto: "100 guantes desechables talla M"
5. Proveedor: "Dental Supply SA"
6. Factura: "FAC-2026-045"
7. Submit
```

### Caso 2: Pago de servicios públicos
```
1. Click en "Registrar Egreso"
2. Monto: $850.00
3. Tipo: "Servicios Públicos"
4. Concepto: "Pago de luz febrero 2026"
5. Proveedor: "CFE"
6. Factura: "CFE-123456789"
7. Submit
```

### Caso 3: Pago de nómina
```
1. Click en "Registrar Egreso"
2. Monto: $5,000.00
3. Tipo: "Nómina"
4. Concepto: "Pago quincenal Dr. García"
5. Submit
```

## Permisos y Seguridad

### Registro de egresos
- Todos los usuarios autenticados pueden registrar egresos
- Se guarda automáticamente el `user_id` del registrador
- Multi-tenant: Solo se ven egresos de la sucursal actual

### Eliminación de egresos
- Solo usuarios con rol `admin` o `superadmin`
- Verificación de permisos en backend
- Validación de multi-tenant (no se puede eliminar de otra sucursal)

### Auditoría
- Modelo usa trait `Auditable`
- Se registran automáticamente:
  - Creación del egreso
  - Actualización del egreso
  - Eliminación del egreso

## Integración con Sistema Existente

### Corte de Caja
El endpoint `/api/finanzas/corte-caja` ahora calcula:

```php
$totalIngresos = Pago::where('clinica_id', $user->clinica_id)
    ->where('sucursal_id', $user->sucursal_id)
    ->whereDate('created_at', $fecha)
    ->sum('monto');

$totalEgresos = Egreso::where('clinica_id', $user->clinica_id)
    ->where('sucursal_id', $user->sucursal_id)
    ->whereDate('created_at', $fecha)
    ->sum('monto');

$balance = $totalIngresos - $totalEgresos;
```

### Reportes Financieros
Los reportes de `ModalCorteFinanzas` ahora pueden incluir:
- Desglose de egresos por tipo
- Gráfica de ingresos vs egresos
- Tendencias de balance diario/semanal/mensual

## Próximas Mejoras Sugeridas

1. **PDF de comprobante de egreso**
   - Similar a ReciboDigital pero para egresos
   - Include datos de proveedor, factura, concepto

2. **Gráficas en Dashboard**
   - Chart.js para visualizar ingresos/egresos/balance
   - Tendencias mensuales

3. **Alertas de gastos**
   - Notificación cuando egresos > X% de ingresos
   - Límites configurables por tipo de egreso

4. **Export a Excel**
   - Reporte de egresos mensual
   - Integración con sistema contable

5. **Adjuntar archivos**
   - Upload de PDFs de facturas
   - Imágenes de comprobantes

## Testing

### Backend
```bash
php artisan test --filter EgresoTest
```

### Frontend
Probar flujo completo:
1. Acceder a vista Caja
2. Click en "Registrar Egreso"
3. Llenar formulario con datos de prueba
4. Verificar que aparece en tabla de egresos
5. Verificar que el balance se actualiza correctamente

### Validaciones a probar
- Monto = 0 → Error
- Tipo vacío → Error
- Concepto vacío → Error
- Concepto > 500 chars → Error
- Campos opcionales pueden estar vacíos → OK

## Troubleshooting

### Error: "Column 'clinica_id' not found"
**Solución**: Ejecutar migración:
```bash
php artisan migrate --path=database/migrations/2026_02_14_020000_create_egresos_table.php
```

### Error: "balance is undefined"
**Solución**: Verificar que el endpoint `/api/finanzas/corte-caja` retorna los campos:
- `total_ingresos`
- `total_egresos`
- `balance`
- `ultimos_egresos`

### Modal no se abre
**Solución**: Verificar imports en Caja.jsx:
```jsx
import ModalRegistrarEgreso from '../components/ModalRegistrarEgreso';
```

### Balance negativo muestra color incorrecto
**Solución**: Verificar condicional en Caja.jsx línea ~265:
```jsx
{(corteCaja?.balance || 0) >= 0 ? 'border-blue-100' : 'border-red-100'}
```

## Contacto y Soporte
Para soporte técnico o dudas sobre el sistema de egresos, consultar la documentación general del proyecto o contactar al equipo de desarrollo.

---
**Versión**: 1.0  
**Fecha**: Febrero 2026  
**Autor**: Sistema de gestión clínica multi-tenant
