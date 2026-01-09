# Sistema de Límites y Rotación de Modelos de IA

## 📊 Resumen del Sistema

Este sistema implementa **rotación automática de modelos** de Google Gemini con **límites por usuario** para controlar costos y evitar agotar las cuotas diarias de la API.

## 🎯 Límites Implementados

### Por Usuario (Configurable en `.env`)
- **Chat (Dr. CardioBot)**: 10 consultas/día por usuario
- **Autocompletar**: 30 peticiones/día por usuario  
- **Resumir**: 20 peticiones/día por usuario

### Modelos Disponibles (Orden de Prioridad)

1. **gemini-2.0-flash** (Primario)
   - Límite: 2,000 requests/día
   - Usado actualmente: 2/2,000 ✅
   - Tokens entrada: 4M/día
   - Tokens salida: Ilimitado

2. **gemini-2.5-flash** (Secundario)
   - Límite: 1,000 requests/día
   - Tokens entrada: 1M/día
   - Tokens salida: 10K/día

3. **gemini-3-flash** (Terciario)
   - Límite: 1,000 requests/día
   - Tokens entrada: 1M/día
   - Tokens salida: 10K/día

4. **gemini-2.5-flash-lite** (Fallback)
   - Límite: 4,000 requests/día
   - Tokens entrada: 4M/día
   - Tokens salida: Ilimitado

## ⚙️ Configuración

### Variables de Entorno (`.env`)

```env
# Google Gemini AI
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=gemini-2.0-flash
GEMINI_TIMEOUT=30

# Límites por usuario/día
AI_CHAT_LIMIT=10
AI_AUTOCOMPLETE_LIMIT=30
AI_SUMMARIZE_LIMIT=20
```

### Modificar Límites

Para cambiar los límites, edita las variables en `.env`:

```bash
# Aumentar límite de chat a 20
AI_CHAT_LIMIT=20

# Reducir autocompletado a 15
AI_AUTOCOMPLETE_LIMIT=15
```

## 🔄 Sistema de Fallback Automático

El sistema intenta los modelos **en orden de prioridad**:

```
gemini-2.0-flash (primario)
    ↓ (si falla por límite 429)
gemini-2.5-flash (secundario)
    ↓ (si falla por límite 429)
gemini-3-flash (terciario)
    ↓ (si falla por límite 429)
gemini-2.5-flash-lite (fallback)
    ↓ (si todos fallan)
Error: "Todos los modelos han alcanzado su límite"
```

### Logs en Laravel

```php
⚡ Intentando con modelo: gemini-2.0-flash
✅ Modelo gemini-2.0-flash funcionó correctamente

// Si falla:
⚠️ Modelo gemini-2.0-flash falló: 429 quota exceeded
⚡ Intentando con modelo: gemini-2.5-flash
✅ Modelo gemini-2.5-flash funcionó correctamente
```

## 📈 Monitoreo de Uso

### Revisar uso diario por usuario

```sql
SELECT 
    u.nombre,
    feature_type,
    COUNT(*) as requests,
    SUM(tokens_used) as total_tokens,
    DATE(created_at) as fecha
FROM ai_usage ai
JOIN users u ON ai.user_id = u.id
WHERE DATE(created_at) = CURDATE()
GROUP BY u.id, feature_type
ORDER BY requests DESC;
```

### Ver modelos más utilizados

```sql
SELECT 
    model_used,
    COUNT(*) as requests,
    SUM(tokens_used) as total_tokens
FROM ai_usage
WHERE DATE(created_at) = CURDATE()
GROUP BY model_used;
```

## 🛡️ Protecciones Implementadas

1. **Límite diario por usuario**: Impide que un usuario agote la cuota
2. **Rotación automática**: Si un modelo alcanza su límite, intenta con el siguiente
3. **Tracking de uso**: Cada petición se registra en `ai_usage` con:
   - Usuario
   - Tipo de feature (chat, autocomplete, summarize)
   - Tokens usados
   - Modelo utilizado
   - Prompt y respuesta

## 🚀 Casos de Uso

### Chat Médico (Dr. CardioBot)
- Usuario pregunta: "¿Cuántas citas tengo hoy?"
- Sistema verifica: Usuario tiene 7/10 consultas usadas hoy ✅
- Intenta con `gemini-2.0-flash` → ✅ Funciona
- Respuesta: "Tienes 3 citas programadas para hoy..."
- Registra uso en DB

### Si alcanza límite diario
- Usuario pregunta: "¿Qué es un ECG?"
- Sistema verifica: Usuario tiene 10/10 consultas usadas hoy ❌
- Respuesta: Error 429 - "Has alcanzado el límite diario de 10 consultas"

### Si modelo alcanza cuota
- Usuario pregunta con `gemini-2.0-flash` (2000/2000 requests)
- API responde: 429 Quota Exceeded
- Sistema automáticamente intenta con `gemini-2.5-flash`
- ✅ Funciona con el segundo modelo
- Usuario no nota la diferencia

## 📊 Estado Actual de Cuotas

| Modelo | Requests | Tokens Entrada | Tokens Salida |
|--------|----------|---------------|---------------|
| gemini-2.0-flash | 2/2K ⚠️ | 1.27K/4M | 5/Ilimitado |
| gemini-2.5-flash | 0/1K ✅ | 0/1M | 0/10K |
| gemini-3-flash | 0/1K ✅ | 0/1M | 0/10K |
| gemini-2.5-flash-lite | 0/4K ✅ | 0/4M | 0/Ilimitado |

## 🔧 Mantenimiento

### Resetear límites diarios (Laravel)

```php
// Ejecutar a medianoche con cron job
DB::table('ai_usage')
    ->whereDate('created_at', '<', now())
    ->delete();
```

### Cambiar modelo por defecto

Edita `config/gemini.php`:

```php
'models' => [
    'primary' => 'gemini-3-flash',      // Nuevo primario
    'secondary' => 'gemini-2.5-flash',
    'tertiary' => 'gemini-2.0-flash',
    'fallback' => 'gemini-2.5-flash-lite',
],
```

## 🎯 Recomendaciones de Producción

1. **Monitorear uso diario**: Revisar logs para detectar patrones
2. **Ajustar límites según demanda**: Si usuarios se quedan cortos, aumentar
3. **Implementar alertas**: Notificar cuando se alcance 80% de cuota global
4. **Considerar plan pagado**: Si se necesita más de 2K requests/día
5. **Rotar API keys**: Si se tiene múltiples keys, distribuir carga

## 📝 Notas Importantes

- Los límites se resetean cada día (medianoche UTC)
- El fallback a modelos lite mantiene calidad similar
- El tracking de uso ayuda a identificar features más populares
- Los usuarios reciben mensajes claros cuando alcanzan límites
