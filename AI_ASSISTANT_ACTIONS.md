# Dr. CardioBot - Sistema de Acciones

## 🤖 Nuevas Capacidades del Asistente

El asistente Dr. CardioBot ahora puede **ejecutar acciones** además de responder preguntas. Puede gestionar citas, crear recordatorios y proporcionar métricas de la clínica.

## 🎯 Acciones Disponibles

### 1. **Cambiar Estado de Cita**
```
Usuario: "Confirma la cita del paciente Juan Pérez"
Asistente: [ACCION:cambiar_estado|cita_id:123|estado:confirmada]
```

**Parámetros:**
- `cita_id`: ID de la cita (obtenido del contexto)
- `estado`: `confirmada`, `pendiente`, `completada`

### 2. **Cancelar Cita**
```
Usuario: "Cancela la cita de mañana a las 10am"
Asistente: [ACCION:cancelar_cita|cita_id:45|motivo:Cancelado por solicitud del usuario]
```

**Parámetros:**
- `cita_id`: ID de la cita
- `motivo`: Razón de la cancelación (opcional)

### 3. **Eliminar Cita**
```
Usuario: "Elimina la cita del 15 de enero"
Asistente: [ACCION:eliminar_cita|cita_id:67]
```

**Parámetros:**
- `cita_id`: ID de la cita

### 4. **Crear Recordatorio/Evento**
```
Usuario: "Recuérdame revisar los resultados mañana a las 3pm"
Asistente: [ACCION:crear_evento|tipo:recordatorio|titulo:Revisar resultados|fecha:2026-01-10|hora:15:00]
```

**Parámetros:**
- `tipo`: `recordatorio`, `tarea`, `evento`
- `titulo`: Título del evento
- `fecha`: Formato YYYY-MM-DD
- `hora`: Formato HH:MM (opcional)
- `descripcion`: Descripción detallada (opcional)
- `color`: Color hex (opcional, default: #3B82F6)

### 5. **Obtener Métricas**
```
Usuario: "Dame un resumen de las métricas de este mes"
Asistente: [ACCION:obtener_metricas]
```

**Respuesta incluye:**
- Total de pacientes
- Citas hoy
- Citas esta semana
- Citas este mes
- Pacientes nuevos del mes
- Citas canceladas del mes

### 6. **Contar Citas de Paciente**
```
Usuario: "¿Cuántas citas ha tenido Lidia Ilvea?"
Asistente: [ACCION:contar_citas_paciente|nombre:Lidia Ilvea]
```

**Parámetros:**
- `nombre`: Nombre completo o solo nombre del paciente

**Respuesta incluye:**
- Total de citas (historial completo)
- Citas completadas
- Citas confirmadas (futuras)
- Citas canceladas
- Citas en los últimos 6 meses
- Última cita (fecha, hora, estado)
- Próxima cita (fecha, hora, estado)

## 🔄 Flujo de Ejecución

### Backend (Laravel)

1. **Usuario envía mensaje** → `/api/ai/chat`
2. **AIService genera respuesta** con comando `[ACCION:...]`
3. **Frontend detecta** el comando en la respuesta
4. **Frontend ejecuta acción** → `/api/ai/action`
5. **AIController procesa** la acción específica
6. **Respuesta confirmación** al usuario

### Diagrama de Flujo

```
Usuario: "Cancela la cita de Juan Pérez"
    ↓
AI detecta intención + busca cita en contexto
    ↓
Respuesta: "Voy a cancelar..." [ACCION:cancelar_cita|cita_id:123]
    ↓
Frontend detecta [ACCION:...]
    ↓
POST /api/ai/action {action: "cancelar_cita", params: {cita_id: 123}}
    ↓
Backend cancela cita en BD
    ↓
✅ "Cita cancelada exitosamente"
```

## 📊 Contexto Disponible para el AI

El asistente tiene acceso a:

```javascript
{
  clinica_id: 1,
  total_pacientes: 150,
  citas_hoy: 8,
  citas_proximas: [
    {
      id: 123,  // ⭐ Incluido para acciones
      fecha: "2026-01-09",
      hora: "10:00",
      paciente: "Juan Pérez",
      estado: "confirmada"
    },
    // ... hasta 20 citas próximas
  ]
}
```

## 💡 Ejemplos de Uso

### Ejemplo 1: Ver Métricas
```
👤 Usuario: "Dame un resumen de cómo va la clínica este mes"

🤖 Dr. CardioBot: "Claro, voy a obtener las métricas actualizadas."

✅ Resultado:
📊 MÉTRICAS DE LA CLÍNICA:

👥 Total de pacientes: 150
📅 Citas hoy: 8
📆 Citas esta semana: 32
📊 Citas este mes: 98
🆕 Pacientes nuevos (mes): 12
❌ Citas canceladas (mes): 5
```

### Ejemplo 2: Cancelar Cita
```
👤 Usuario: "Cancela la cita de María González de mañana"

🤖 Dr. CardioBot: "Voy a cancelar la cita de María González programada para mañana 10 de enero a las 2pm."

✅ "Cita cancelada exitosamente"
```

### Ejemplo 3: Crear Recordatorio
```
👤 Usuario: "Recuérdame llamar al laboratorio pasado mañana a las 11am"

🤖 Dr. CardioBot: "Perfecto, he creado un recordatorio para el 11 de enero a las 11am para llamar al laboratorio."

✅ "Evento creado exitosamente"
```

### Ejemplo 4: Cambiar Estado
```
👤 Usuario: "Marca como completada la cita de las 3pm"

🤖 Dr. CardioBot: "He cambiado el estado de la cita de las 3pm a completada."

✅ "Estado de cita actualizado exitosamente"
```

### Ejemplo 5: Contar Citas de Paciente
```
👤 Usuario: "¿Cuántas citas ha tenido Lidia Ilvea?"

🤖 Dr. CardioBot: "Voy a consultar el historial de citas de Lidia Ilvea."

✅ Resultado:
📊 HISTORIAL DE CITAS - Lidia Ilvea García (Exp: 12345)

📈 Total de citas: 15
✅ Completadas: 12
📅 Confirmadas (futuras): 2
❌ Canceladas: 1
📊 Últimos 6 meses: 8

🕐 Última cita: 15/01/2026 a las 10:00 (Completada)
📅 Próxima cita: 22/01/2026 a las 14:30 (Confirmada)
```

### Ejemplo 6: Búsqueda Simple
```
👤 Usuario: "cuenta cuantas citas ha tenido Maria"

🤖 Dr. CardioBot: "Buscando historial de citas de María..."

✅ María López Martínez ha tenido 8 citas en total. 6 completadas, 1 confirmada próximamente y 1 cancelada.
```

## 🔒 Seguridad

### Validaciones Implementadas

1. **Permisos por Clínica**
   - Solo se pueden modificar citas de la clínica del usuario
   - `where('clinica_id', $user->clinica_id)`

2. **Límites de Acciones**
   - Mismos límites que el chat (10 requests/día)
   - Cada acción cuenta como 1 request

3. **Validación de Parámetros**
   - Todos los parámetros son validados en el backend
   - IDs de citas deben existir y pertenecer a la clínica

4. **Logging**
   - Todas las acciones se registran en `ai_usage`
   - Incluye: usuario, acción, parámetros, resultado

## 🛠️ Desarrollo

### Agregar Nueva Acción

1. **Crear método en AIController**
```php
private function nuevaAccion($user, $params)
{
    // Validar parámetros
    // Ejecutar lógica
    // Retornar respuesta
}
```

2. **Agregar al switch de executeAction**
```php
case 'nueva_accion':
    return $this->nuevaAccion($user, $params);
```

3. **Actualizar prompt del AI en AIService**
```
- Nueva acción: [ACCION:nueva_accion|param1:valor|param2:valor]
```

4. **Documentar en este archivo**

## 📝 Logs de Ejemplo

```
[2026-01-09 14:30:15] INFO: 💬 Chat médico
[2026-01-09 14:30:15] INFO: Ejecutando acción: cancelar_cita
[2026-01-09 14:30:15] INFO: ✅ Cita 123 cancelada exitosamente
```

## 🚀 Próximas Mejoras

- [x] Contar citas de un paciente específico (historial completo)
- [ ] Modificar horarios de citas existentes
- [ ] Generar reportes PDF de métricas
- [ ] Enviar notificaciones a pacientes
- [ ] Búsqueda avanzada de pacientes por diagnóstico
- [ ] Estadísticas personalizadas por doctor
- [ ] Exportar historial de citas a Excel/CSV

## 📊 Métricas de Uso

Para ver qué acciones se usan más:

```sql
SELECT 
    JSON_EXTRACT(prompt, '$.action') as accion,
    COUNT(*) as total,
    DATE(created_at) as fecha
FROM ai_usage
WHERE feature_type = 'chat'
  AND prompt LIKE '%ACCION%'
GROUP BY accion, fecha
ORDER BY total DESC;
```

## ⚠️ Limitaciones Actuales

1. **Acciones Secuenciales**: Solo una acción por mensaje
2. **Sin Confirmación**: El AI ejecuta inmediatamente (mejora futura: pedir confirmación)
3. **Contexto Limitado**: Solo próximos 7 días de citas (configurable)
4. **Sin Undo**: Acciones no son reversibles automáticamente
