# Demos de clínicas (Rehabilitación y Dental)

Comandos para crear clínicas de demostración con datos completos: clínica, sucursal, usuarios, pacientes, citas, pagos y expedientes según el tipo. **Idempotentes**: si el demo ya existe (mismo email de clínica), no se modifica nada.

---

## 1. Demo Dental

Ver **[DEMO_DENTAL.md](DEMO_DENTAL.md)** para detalles.

```bash
php artisan demo:create-dental
```

| Rol   | Email                               | Contraseña  |
|-------|-------------------------------------|-------------|
| Admin | `admin@demo-dental.pacientesrhc`    | `Demo2025!` |
| Doctor| `dr.garcia@demo-dental.pacientesrhc`| `Demo2025!` |

---

## 2. Demos de Rehabilitación (Cardiopulmonar y Fisioterapia)

Un solo comando con tres variantes de **tipo**:

```bash
php artisan demo:create-rehab {tipo}
```

**Valores de `tipo`:**

| Tipo                   | Descripción |
|------------------------|-------------|
| `cardiopulmonar-fisio` | Rehabilitación cardiopulmonar **y** fisioterapia (pacientes cardíacos, pulmonares, ambos y fisioterapia; expedientes de todo tipo) |
| `cardiopulmonar`       | Solo rehabilitación cardiopulmonar (pacientes cardíacos, pulmonares y ambos; sin expedientes de fisio) |
| `fisioterapia`         | Solo fisioterapia (pacientes tipo fisioterapia; expedientes fisio: historia, evolución, alta, reporte) |

### Ejemplos

```bash
# Clínica con cardiopulmonar + fisioterapia
php artisan demo:create-rehab cardiopulmonar-fisio

# Solo cardiopulmonar
php artisan demo:create-rehab cardiopulmonar

# Solo fisioterapia
php artisan demo:create-rehab fisioterapia
```

### Qué crea cada demo de rehab

- **1 clínica** (tipo `rehabilitacion_cardiopulmonar` o `fisioterapia` según corresponda)
- **1 sucursal** (Sucursal Principal)
- **2 usuarios**: Admin y Doctor/Fisioterapeuta
- **10 pacientes** con tipos coherentes al demo (cardiaca, pulmonar, ambos, fisioterapia)
- **15 citas** (pasadas y futuras, varios estados)
- **10 pagos** (efectivo, tarjeta, transferencia; algunos ligados a citas completadas)
- **Expedientes de demostración** según tipo:
  - **Cardiopulmonar (+ fisio opcional):** clínico, esfuerzo, estratificación, reporte final, reporte nutri, reporte psico; si aplica también reporte fisio, historia clínica fisio, nota evolución, nota alta
  - **Solo fisioterapia:** reporte fisio, historia clínica fisioterapia, nota evolución, nota alta

### Credenciales por tipo de demo

Contraseña común: **`Demo2025!`**

| Tipo                   | Email clínica (identificador)        | Admin (email)                              | Doctor/Fisio (email)                          |
|------------------------|--------------------------------------|--------------------------------------------|-----------------------------------------------|
| cardiopulmonar-fisio   | `demo-cardiopulmonar-fisio@demo.pacientesrhc` | `admin@demo-cardiopulmonar-fisio.pacientesrhc` | `dr.martinez@demo-cardiopulmonar-fisio.pacientesrhc` |
| cardiopulmonar         | `demo-cardiopulmonar@demo.pacientesrhc`       | `admin@demo-cardiopulmonar.pacientesrhc`       | `dr.martinez@demo-cardiopulmonar.pacientesrhc`     |
| fisioterapia           | `demo-fisioterapia@demo.pacientesrhc`         | `admin@demo-fisioterapia.pacientesrhc`         | `dr.martinez@demo-fisioterapia.pacientesrhc`       |

Iniciar sesión con el email de Admin o de Doctor/Fisio y, si aparece, elegir la sucursal **Sucursal Principal**.

### Notas

- Los emails de clínica (`demo-*@demo.pacientesrhc`) son los que usa el comando para detectar si el demo ya existe; no conviene cambiarlos si se quiere conservar la idempotencia.
- Los pacientes usan correos de ejemplo (`@ejemplo.com`); no se envían correos reales.
- Para quitar un demo, eliminar manualmente la clínica (y en cascada sus datos) desde el panel o la base de datos.

---

## Encriptación y producción

Los datos de pacientes, expedientes y pagos usan **encriptación de Laravel** (campos `encrypted` con la `APP_KEY` del `.env`).

- **Crear el demo en el mismo entorno donde se usará.** Si generas el demo en local y luego subes la base de datos a producción (o usas otra `APP_KEY`), los campos encriptados no se podrán desencriptar y verás errores o datos ilegibles.
- **Recomendación:** ejecutar los comandos demo **en el propio servidor de producción** (o staging) cuando quieras tener demos ahí. Así la encriptación usa la `APP_KEY` de ese entorno y todo se lee correctamente.
- No importar/restaurar una base de datos con demos creados en otro entorno si la `APP_KEY` es distinta.
