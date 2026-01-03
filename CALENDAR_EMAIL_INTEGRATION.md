# Integración de Calendario con Email (.ics)

## Descripción General

Se ha implementado un sistema completo para enviar invitaciones de calendario por email a los doctores cuando se crean, actualizan o cancelan citas. Las invitaciones utilizan el estándar iCalendar (.ics) que es compatible con todos los clientes de correo modernos (Outlook, Apple Mail, Gmail, Thunderbird, etc.).

## Componentes Implementados

### 1. Librería eluceo/ical (v2.15.0)
- **Instalada via Composer**: `eluceo/ical`
- **Propósito**: Generar archivos .ics compatibles con el estándar RFC 5545
- **Ubicación**: `/vendor/eluceo/ical`

### 2. CalendarService (app/Services/CalendarService.php)
Servicio encargado de generar archivos .ics con la información de las citas.

**Métodos principales:**
- `generateIcs($cita, $action)`: Genera el contenido .ics para una cita
- `createEvent($cita, $paciente, $doctor, $action)`: Crea el evento de calendario
- `getSummary($cita, $paciente)`: Genera el título del evento
- `getDescription($cita, $paciente, $doctor)`: Genera la descripción detallada
- `generateCancellationIcs($cita)`: Genera .ics para cancelación
- `generateUpdateIcs($cita)`: Genera .ics para actualización

**Características:**
- UID único por cita: `cita-{id}@{app.url}`
- Duración predeterminada: 1 hora
- Organizador: Email del doctor
- Ubicación: Dirección de la clínica
- Secuencia incrementada en actualizaciones

### 3. CitaInvitationMail (app/Notifications/CitaInvitationMail.php)
Notificación que envía el email con el archivo .ics adjunto.

**Características:**
- Envío por canal `mail`
- Archivo .ics adjunto con MIME type correcto
- Tres tipos de mensajes:
  - **CREATE**: "Nueva Cita Agendada" (METHOD:REQUEST)
  - **UPDATE**: "Cita Actualizada" (METHOD:REQUEST)
  - **CANCEL**: "Cita Cancelada" (METHOD:CANCEL)
- Incluye información del paciente, fecha, hora y notas
- Personalizado con nombre de la clínica

### 4. CitaController Actualizado
Se actualizaron 3 métodos principales:

#### `store()` - Crear cita
```php
// Después de crear la cita
$this->sendCalendarInvitation($cita, 'create');
```

#### `update()` - Actualizar cita
```php
// Solo si cambió fecha u hora
if ($request->has('fecha') || $request->has('hora')) {
    $this->sendCalendarInvitation($cita, 'update');
}
```

#### `destroy()` - Eliminar cita
```php
// Antes de eliminar la cita
$this->sendCalendarInvitation($cita, 'cancel');
$cita->delete();
```

**Nuevo método privado:**
- `sendCalendarInvitation($cita, $action)`: Envía la notificación al doctor

## Flujo de Funcionamiento

### 1. Crear Cita
1. Usuario crea una cita desde el frontend (Calendar.jsx)
2. **NUEVO**: Opcionalmente puede especificar `user_id` (doctor que recibirá la invitación)
3. Backend guarda la cita en la base de datos con el campo `user_id`
4. `sendCalendarInvitation($cita, 'create')` se ejecuta
5. Se obtiene el doctor especificado en `cita->user_id` (o el del paciente como fallback)
6. Se envía notificación con archivo .ics adjunto al doctor seleccionado
7. Doctor recibe email con invitación
8. Cliente de correo muestra botones "Aceptar" / "Agregar a Calendario"
9. Al hacer clic, el evento se agrega al calendario del doctor

### 2. Actualizar Cita
1. Usuario actualiza fecha u hora de una cita
2. Backend actualiza la cita en la base de datos
3. `sendCalendarInvitation($cita, 'update')` se ejecuta
4. Se envía .ics con SEQUENCE incrementada
5. Cliente de correo actualiza el evento existente en el calendario

### 3. Cancelar/Eliminar Cita
1. Usuario elimina una cita
2. `sendCalendarInvitation($cita, 'cancel')` se ejecuta ANTES de eliminar
3. Se envía .ics con METHOD:CANCEL
4. Cliente de correo elimina el evento del calendario
5. Backend elimina la cita de la base de datos

## Configuración Requerida

### Configuración de Email (ya existente)
Asegúrate de tener configurado el envío de emails en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Verificar que los doctores tengan email
```sql
-- Verificar que los usuarios (doctores) tengan email válido
SELECT id, nombre, apellidoPat, email FROM users WHERE email IS NOT NULL;
```

### Verificar que los pacientes estén asignados a doctores
```sql
-- Verificar relación paciente -> doctor
SELECT p.id, p.nombre, p.apellidoPat, u.email as doctor_email 
FROM pacientes p 
LEFT JOIN users u ON p.user_id = u.id;
```

## Cómo Probar

### 1. Crear una cita de prueba
```javascript
// Desde Calendar.jsx en el frontend
// 1. Selecciona un paciente existente
// 2. Selecciona fecha y hora
// 3. **NUEVO**: Selecciona el doctor que recibirá la invitación
//    - Deja en blanco para usar el doctor del paciente por defecto
//    - O selecciona un doctor específico del dropdown
// 4. Haz clic en "Guardar Cita"
// 5. Revisa el email del doctor seleccionado
```

### 2. Frontend: Selector de Doctor
El formulario de citas ahora incluye un dropdown para seleccionar el doctor:

```jsx
<select value={formData.user_id} onChange={...}>
  <option value="">Por defecto (doctor del paciente)</option>
  <option value="1">Dr. García Pérez</option>
  <option value="2">Dr. Martínez López</option>
  ...
</select>
```

### 3. Verificar logs
```bash
# En el servidor
tail -f storage/logs/laravel.log | grep "Calendar invitation"
```

Deberías ver:
```
Calendar invitation sent to doctor: doctor@example.com for cita 123 (action: create)
```

### 3. Revisar el email recibido
El doctor debería recibir un email con:
- Asunto: "Nueva Cita - DD/MM/YYYY HH:MM"
- Cuerpo: Información del paciente y detalles de la cita
- Adjunto: `cita-123.ics`
- Botones: "Aceptar" o "Agregar a Calendario" (según el cliente de email)

### 4. Importar a calendario
Al abrir el archivo .ics o hacer clic en "Aceptar":
- **Outlook**: Aparece ventana de confirmación, se agrega al calendario
- **Apple Mail/Calendar**: Botón "Agregar a Calendario" → se agrega
- **Gmail**: Botón "Agregar a Google Calendar" → se agrega
- **Thunderbird**: Opción "Aceptar" → se agrega a Lightning Calendar

### 5. Probar actualización
1. Edita la cita cambiando fecha u hora
2. Guarda cambios
3. El doctor recibe email "Cita Actualizada"
4. El evento se actualiza automáticamente en su calendario

### 6. Probar cancelación
1. Elimina una cita existente
2. El doctor recibe email "Cita Cancelada"
3. El evento se elimina automáticamente de su calendario

## Compatibilidad de Clientes de Email

| Cliente | Crear | Actualizar | Cancelar | Notas |
|---------|-------|------------|----------|-------|
| Outlook (Desktop) | ✅ | ✅ | ✅ | Soporte completo |
| Outlook (Web) | ✅ | ✅ | ✅ | Soporte completo |
| Apple Mail/Calendar | ✅ | ✅ | ✅ | Soporte completo |
| Gmail | ✅ | ✅ | ✅ | Botón "Agregar a Google Calendar" |
| Thunderbird + Lightning | ✅ | ✅ | ✅ | Soporte completo |
| iOS Mail | ✅ | ✅ | ✅ | Se sincroniza con iCloud Calendar |
| Android Gmail | ✅ | ✅ | ✅ | Se sincroniza con Google Calendar |

## Ventajas de esta Implementación

1. **Sin dependencias externas**: No requiere API de Google Calendar ni autenticación OAuth
2. **Estándar abierto**: Usa RFC 5545 (iCalendar) compatible con todos los clientes
3. **Privacidad**: Los datos permanecen en tu servidor
4. **Simplicidad**: El doctor solo recibe un email, no necesita iniciar sesión en nada
5. **Sincronización automática**: CREATE/UPDATE/CANCEL se manejan automáticamente
6. **Multi-calendario**: Funciona con cualquier calendario del doctor (personal, trabajo, etc.)
7. **Offline-first**: El archivo .ics se puede guardar y usar después

## Desventajas vs Google Calendar API

1. **Sin sincronización bidireccional**: Si el doctor edita/elimina el evento en su calendario, no se refleja en la app
2. **Sin confirmación de asistencia**: No sabemos si el doctor aceptó/rechazó la cita
3. **Sin disponibilidad en tiempo real**: No podemos ver el calendario del doctor para evitar conflictos
4. **Dependiente de email**: Si el email no llega, la invitación no se recibe

## Troubleshooting

### Email no se envía
```bash
# Verificar configuración de email
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

### Archivo .ics no se genera
```php
// En tinker
$cita = App\Models\Cita::first();
$service = new App\Services\CalendarService();
$ics = $service->generateIcs($cita, 'create');
echo $ics;
```

### Doctor no tiene email
```sql
-- Actualizar email del doctor
UPDATE users SET email = 'doctor@example.com' WHERE id = 1;
```

### Logs de errores
```bash
tail -f storage/logs/laravel.log
```

## Siguiente Paso: Producción

### 1. Deploy del código
```bash
# En producción
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Configurar email en producción
- Usa un servicio SMTP confiable (SendGrid, Mailgun, SES, etc.)
- Configura SPF, DKIM y DMARC para evitar spam

### 3. Monitorear logs
```bash
# Crear alerta para errores de calendar invitation
grep "Error sending calendar invitation" storage/logs/laravel.log
```

### 4. (Opcional) Cola de trabajos
Para no bloquear la respuesta HTTP:
```php
// En .env
QUEUE_CONNECTION=database

// Crear tabla de trabajos
php artisan queue:table
php artisan migrate

// Ejecutar worker
php artisan queue:work
```

## Archivos Modificados

1. ✅ `composer.json` - Agregada dependencia eluceo/ical
2. ✅ `app/Services/CalendarService.php` - Nuevo servicio
3. ✅ `app/Notifications/CitaInvitationMail.php` - Nueva notificación
4. ✅ `app/Http/Controllers/CitaController.php` - Actualizado store(), update(), destroy()
5. ✅ `app/Models/Cita.php` - Agregado campo `user_id` y relación `user()`
6. ✅ `database/migrations/2026_01_02_205235_add_user_id_to_citas_table.php` - Nueva migración

## Campos de la Tabla `citas`

- `paciente_id` - Paciente de la cita
- `admin_id` - Usuario que creó/registró la cita
- **`user_id` (NUEVO)** - Doctor que recibirá la invitación de calendario (nullable)
- `clinica_id` - Clínica donde se realiza la cita
- `fecha` - Fecha de la cita
- `hora` - Hora de la cita
- `estado` - Estado (pendiente, confirmada, cancelada, completada)
- `primera_vez` - Si es primera consulta
- `notas` - Notas adicionales

## Selección de Doctor

Al crear o actualizar una cita, puedes especificar el doctor que recibirá la invitación de calendario:

```javascript
// Ejemplo desde el frontend
const citaData = {
  paciente_id: 123,
  user_id: 456,  // NUEVO: ID del doctor que recibirá la invitación
  fecha: '2026-01-15',
  hora: '10:00',
  estado: 'confirmada',
  notas: 'Primera consulta'
};
```

**Lógica de fallback:**
1. Si `user_id` está especificado → se envía la invitación a ese doctor
2. Si `user_id` es null → se envía al doctor del paciente (`paciente.user_id`)
3. Si ninguno existe → no se envía invitación (se registra en logs)

## Resultado Final

Los doctores ahora recibirán automáticamente invitaciones de calendario por email cada vez que:
- Se cree una cita con uno de sus pacientes
- Se actualice la fecha/hora de una cita existente
- Se cancele/elimine una cita

Las invitaciones se agregarán automáticamente a su calendario de email (Outlook, Apple Calendar, Gmail, etc.) sin necesidad de autenticación adicional ni dependencias de Google Calendar.
