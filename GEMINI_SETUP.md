# 🤖 Configuración de Google Gemini

La aplicación ahora usa **Google Gemini Pro** en lugar de OpenAI para las funcionalidades de IA.

## ✅ Ventajas de Gemini

- 💰 **Más económico** que OpenAI GPT-4
- 🆓 **Nivel gratuito generoso**: 60 solicitudes por minuto gratis
- 🚀 **Rendimiento similar** a GPT-3.5/GPT-4
- 🌐 **Sin limitaciones regionales**

## 📝 Paso 1: Obtener API Key de Google

1. Ve a [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Inicia sesión con tu cuenta de Google
3. Click en **"Get API Key"** o **"Create API Key"**
4. Copia la API Key que te proporcione

**Nota:** Como tienes Gemini Pro, tu API key ya tiene acceso completo.

## 🔧 Paso 2: Configurar en Laravel

Abre tu archivo `.env` y agrega:

```env
# Google Gemini Configuration
GEMINI_API_KEY=tu-api-key-aqui
GEMINI_MODEL=gemini-pro
GEMINI_TEMPERATURE=0.7
GEMINI_MAX_TOKENS=1000
GEMINI_TIMEOUT=30
```

**Ejemplo:**
```env
GEMINI_API_KEY=AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

## 🧹 Paso 3: Limpiar caché

```bash
php artisan config:clear
php artisan cache:clear
```

## ✨ Funcionalidades que usa Gemini

1. **Autocompletado Inteligente** 
   - En todos los formularios (Clínico, Nutri, Psico, Fisio, etc.)
   - ~34 campos con SmartTextarea

2. **Dashboard de Insights**
   - Reportes ejecutivos semanales
   - Análisis automático de pacientes
   - Alertas y recomendaciones

3. **Resúmenes de Reportes**
   - Resúmenes automáticos de reportes largos
   - Información clave extraída

## 📊 Límites de la API Gratuita

- **60 solicitudes por minuto** (más que suficiente para la app)
- Sin costo hasta cierto límite mensual
- Después, costo muy bajo por solicitud

## 🎤 Transcripción de Voz (Gratis)

La transcripción de voz **NO usa Gemini**, usa **Web Speech API** del navegador:
- ✅ Completamente gratis
- ✅ Sin consumir cuota de API
- ✅ Funciona en Chrome y Edge

## 🚨 Troubleshooting

### Error: "API Key no configurada"
- Verifica que agregaste `GEMINI_API_KEY` en `.env`
- Ejecuta `php artisan config:clear`

### Error: "API Key inválida"
- Verifica que copiaste la key completa
- Asegúrate de no tener espacios extras
- Genera una nueva key en Google AI Studio

### Error: "Respuesta inválida"
- Verifica tu conexión a internet
- Asegúrate de usar `gemini-pro` como modelo
- Revisa los logs: `tail -f storage/logs/laravel.log`

## 📞 Soporte

Si tienes problemas, revisa los logs:

```bash
tail -n 50 storage/logs/laravel.log | grep -i gemini
```

Busca emojis en los logs:
- 🤖 = Llamada iniciada
- ✅ = Éxito
- ❌ = Error
- ⚠️ = Advertencia

## 🔄 Migración desde OpenAI

Si tenías OpenAI configurado:

1. **Puedes mantener ambos** (opcional):
   ```env
   OPENAI_API_KEY=sk-xxx...
   GEMINI_API_KEY=AIza...
   ```

2. **O remover OpenAI**:
   ```bash
   composer remove openai-php/laravel
   ```

3. **Limpiar configuración**:
   ```bash
   rm config/openai.php
   php artisan config:clear
   ```

La aplicación **ya está configurada para usar Gemini** por defecto. Solo necesitas agregar tu API key.
