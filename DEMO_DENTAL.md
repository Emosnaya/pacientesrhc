# Demo Clínica Dental

Demo profesional de la aplicación para una **clínica dental**, con datos de prueba listos para producción sin afectar datos existentes.

## Qué crea el comando

- **1 clínica** tipo `dental` (Clínica Dental Demo)
- **1 sucursal** (Sucursal Principal - Del Valle)
- **2 usuarios**: Admin y Doctor
- **10 pacientes** con datos realistas (nombres, teléfonos, emails, motivo consulta, tipo odontología general/ortodoncia)
- **10 historias clínicas dentales** (una por paciente) y **10 odontogramas** (uno por paciente, con dientes FDI inicializados)
- **23 citas**: pasadas (varios estados) y futuras (pendientes/confirmadas)
- **12 pagos**: efectivo, tarjeta, transferencia; algunos ligados a citas completadas

## Cómo ejecutarlo (producción o local)

```bash
cd /ruta/al/proyecto/pacientesrhc
php artisan demo:create-dental
```

- **Seguro para producción**: si la clínica demo ya existe (mismo email), el comando no hace nada y no modifica datos.
- No elimina ni altera clínicas, usuarios ni pacientes existentes.

### Si ya corriste el demo antes (sin historias ni odontogramas)

Si creaste el demo dental con una versión anterior del comando y tus pacientes no tienen historia clínica dental ni odontograma, ejecuta:

```bash
php artisan demo:completar-dental
```

Esto añade **solo** la historia clínica dental y el odontograma a cada paciente del demo que aún no los tenga. No borra ni modifica nada más.

### Cargar sucursales adicionales (10 pacientes por sucursal)

Si ya creaste sucursales extra (p. ej. Miramontes, Zaragoza) y quieres tener **10 pacientes por sucursal** con historia clínica, odontograma, pagos y **2 recetas por paciente**, ejecuta:

```bash
php artisan demo:cargar-sucursales-dental
```

- Recorre **todas** las sucursales de la clínica demo dental.
- Por cada sucursal que tenga **menos de 10 pacientes**, crea los que falten hasta 10.
- Para cada paciente nuevo: historia clínica dental, odontograma (dientes FDI), 1 pago y 2 recetas (cada una con un medicamento de ejemplo).
- Opción: `--por-sucursal=15` para usar 15 pacientes por sucursal en lugar de 10.

## Credenciales de acceso (demo)

| Rol    | Email                          | Contraseña |
|--------|---------------------------------|------------|
| Admin  | `admin@demo-dental.pacientesrhc` | `Demo2025!` |
| Doctor | `dr.garcia@demo-dental.pacientesrhc` | `Demo2025!` |

Iniciar sesión en la aplicación con cualquiera de estos usuarios. Tras el login, seleccionar la sucursal **Sucursal Principal - Del Valle** si se muestra el selector de sucursales.

## Flujo de la demo

1. **Pacientes**: listado con 10 pacientes, historial dental y odontogramas.
2. **Calendario**: citas pasadas y futuras.
3. **Caja / Finanzas**: pagos registrados, cortes de caja y recibos.
4. **Analíticas**: si aplica para tipo dental, métricas de citas y pagos.

## Notas

- La clínica demo se identifica por el email `demo-dental@demo.pacientesrhc`. No cambiar este email si se quiere conservar el comportamiento idempotente del comando.
- Los pacientes usan emails de ejemplo (`@ejemplo.com`); no se envían correos reales.
- Para eliminar la demo en el futuro, borrar manualmente la clínica (y en cascada sucursal, usuarios, pacientes, citas y pagos) desde el panel de administración o la base de datos.

**Encriptación:** Los datos sensibles se guardan encriptados con la `APP_KEY` de Laravel. Crea el demo en el mismo entorno donde lo usarás (p. ej. ejecuta el comando en producción si quieres la demo en producción). Si importas una base de datos de otro entorno con otra `APP_KEY`, los campos encriptados no se podrán leer. Ver también [DEMO_CLINICAS.md](DEMO_CLINICAS.md#encriptación-y-producción).
