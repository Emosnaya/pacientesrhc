# Importación con Laravel Artisan (Método Recomendado)

## ✨ La forma más sencilla y segura

Usa el comando artisan de Laravel con transacciones automáticas y rollback.

## Preparación (solo una vez)

1. **Sube estos archivos a tu servidor Hostinger:**

```bash
# Via SFTP o File Manager de Hostinger:
/public_html/database/imports/import_bulk.sql
/public_html/database/imports/import_esfuerzos.sql
/public_html/database/imports/import_estratificaciones2.sql

# Y el comando:
/public_html/app/Console/Commands/ImportBulkData.php
```

## Ejecución

### Opción 1: Con acceso SSH (ideal)

```bash
# Conectar a Hostinger via SSH
ssh u123456789@tu-dominio.com

# Ir a la carpeta de tu proyecto
cd public_html

# Ejecutar la importación
php artisan import:bulk
```

El comando te preguntará si deseas continuar y luego te mostrará una verificación antes de confirmar.

### Opción 2: Sin SSH - Crear ruta web temporal

Crea el archivo `public_html/routes/web.php` y agrega:

```php
Route::get('/admin/import-bulk', function() {
    if (request()->get('token') !== 'tu-secreto-seguro-123') {
        abort(403);
    }
    
    Artisan::call('import:bulk');
    return '<pre>' . Artisan::output() . '</pre>';
})->middleware('auth'); // O sin middleware si no tienes login
```

Luego visita:
```
https://tu-dominio.com/admin/import-bulk?token=tu-secreto-seguro-123
```

**⚠️ Elimina esta ruta después de usarla**

## Comandos disponibles

### Importar datos
```bash
php artisan import:bulk
```
- Crea backup automático
- Usa transacciones
- Verifica antes de confirmar
- Te pregunta antes de guardar cambios

### Solo verificar (sin importar)
```bash
php artisan import:bulk --verify
```
Muestra cuántos registros hay actualmente sin hacer cambios.

### Revertir importación
```bash
php artisan import:bulk --rollback
```
Elimina TODOS los registros con user_id=3 y clinica_id=1.

## ✅ Ventajas de este método

1. **Transacciones automáticas**: Si algo falla, se revierte solo
2. **Verificación antes de confirmar**: Ves los totales antes de guardar
3. **Backup automático**: Guarda IDs antes de importar
4. **Interactivo**: Te pregunta antes de hacer cambios
5. **Fácil rollback**: Un comando para revertir todo
6. **No requiere acceso a MySQL directo**

## 📋 Proceso completo

```bash
# 1. Verificar estado actual
php artisan import:bulk --verify

# 2. Ejecutar importación
php artisan import:bulk
# > Te pregunta: ¿Proceder? [yes/no]
# > Importa los 3 archivos en orden
# > Muestra tabla de verificación:
#   Pacientes:         136 / 136 ✅
#   Clínicos:          136 / 136 ✅
#   Esfuerzos:         240 / 240 ✅
#   Estratificaciones: 136 / 136 ✅
# > Te pregunta: ¿Confirmar? [yes/no]

# 3. Si confirmaste, ¡listo! Los datos están guardados
# 4. Si algo salió mal, puedes revertir:
php artisan import:bulk --rollback
```

## 🔍 Qué hace internamente

1. **Verifica archivos**: Confirma que existen los 3 SQL
2. **Crea backup**: Guarda IDs de registros existentes
3. **Inicia transacción**: `DB::beginTransaction()`
4. **Ejecuta SQLs**: En orden (bulk → esfuerzos → estratificaciones)
5. **Verifica totales**: Consulta cuántos registros se importaron
6. **Espera confirmación**: Te muestra los resultados
7. **Commit o Rollback**: Según tu respuesta

## 🚨 Si algo sale mal

El comando hace rollback automático si:
- Un archivo SQL tiene error de sintaxis
- Falla una inserción
- No se confirman los cambios
- Ocurre cualquier excepción

Los datos anteriores **permanecen intactos**.

## 📦 Backups

Los backups se guardan en:
```
storage/app/backups/YYYY-MM-DD_HH-ii-ss/
  ├── pacientes_count.txt
  ├── pacientes_ids.json
  ├── clinicos_count.txt
  ├── clinicos_ids.json
  ├── esfuerzos_count.txt
  ├── esfuerzos_ids.json
  ├── estratificacions_count.txt
  └── estratificacions_ids.json
```

## 🎯 Ejemplo de salida

```
🚀 Iniciando importación de datos bulk...

📦 Creando backup de seguridad...
   Backup ID: 2025-12-14_15-30-45

 ¿Proceder con la importación? (yes/no) [yes]:
 > yes

📝 Paso 1/3: Importando import_bulk.sql...
   ✅ Completado

📝 Paso 2/3: Importando import_esfuerzos.sql...
   ✅ Completado

📝 Paso 3/3: Importando import_estratificaciones2.sql...
   ✅ Completado

🔍 Verificando importación...
+-------------------+-------+----------+--------+
| Tabla             | Total | Esperado | Estado |
+-------------------+-------+----------+--------+
| Pacientes         | 136   | 136      | ✅     |
| Clínicos          | 136   | 136      | ✅     |
| Esfuerzos         | 240   | 240      | ✅     |
| Estratificaciones | 136   | 136      | ✅     |
+-------------------+-------+----------+--------+

 ✅ Verificación exitosa. ¿Confirmar los cambios? (yes/no) [yes]:
 > yes

🎉 Importación completada exitosamente
   Backup guardado con ID: 2025-12-14_15-30-45
   Para revertir: php artisan import:bulk --rollback
```

## 🔄 Para revertir

```bash
php artisan import:bulk --rollback

# Muestra tabla con registros que eliminará
# Te pregunta confirmación
# Elimina en orden inverso: estratificaciones → esfuerzos → clínicos → pacientes
```

---

**✨ Este es el método más sencillo y seguro para Hostinger**
