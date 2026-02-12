# Demo Clínica Dental

Demo profesional de la aplicación para una **clínica dental**, con datos de prueba listos para producción sin afectar datos existentes.

## Qué crea el comando

- **1 clínica** tipo `dental` (Clínica Dental Demo)
- **1 sucursal** (Sucursal Principal - Del Valle)
- **2 usuarios**: Admin y Doctor
- **10 pacientes** con datos realistas (nombres, teléfonos, emails, motivo consulta, tipo odontología general/ortodoncia)
- **23 citas**: pasadas (varios estados) y futuras (pendientes/confirmadas)
- **12 pagos**: efectivo, tarjeta, transferencia; algunos ligados a citas completadas

## Cómo ejecutarlo (producción o local)

```bash
cd /ruta/al/proyecto/pacientesrhc
php artisan demo:create-dental
```

- **Seguro para producción**: si la clínica demo ya existe (mismo email), el comando no hace nada y no modifica datos.
- No elimina ni altera clínicas, usuarios ni pacientes existentes.


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
