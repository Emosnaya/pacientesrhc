# Bulk de datos dummy – Pacientes dentales con historia clínica y odontograma

## Por qué no usar solo SQL en producción

En esta aplicación, **Paciente**, **Historia Clinica Dental** y **Odontograma** tienen campos **encriptados** con la `APP_KEY` de Laravel (nombre, apellidos, teléfono, email, domicilio, motivo_consulta, alergias, contenido de historia, diagnóstico/pronóstico/observaciones del odontograma, etc.).

- Si insertas esos datos con **SQL puro**, se guardan en **texto plano**.
- Cuando la app lee los registros, Laravel intenta **desencriptar** y falla o muestra datos incorrectos.
- Por tanto, en **producción no se deben usar INSERT directos** en `pacientes`, `historia_clinica_dental` ni `odontogramas` para datos que deban verse bien en la app.

## Método recomendado (seguro para producción)

Usa el comando Artisan que crea los registros con **Eloquent**, de modo que la encriptación se aplica al guardar y todo queda consistente:

```bash
# Clínica 4, todas las sucursales activas, 30 pacientes en total (repartidos)
php artisan demo:seed-dummy-dental-bulk 4 --sucursales=all --total=30

# Clínica 4, solo sucursales 5 y 6, 20 pacientes
php artisan demo:seed-dummy-dental-bulk 4 --sucursales=5,6 --total=20

# Por defecto: total=30, sucursales=all
php artisan demo:seed-dummy-dental-bulk 4
```

- **No borra ni modifica** datos existentes.
- Reparte los pacientes entre las sucursales indicadas (round-robin).
- Por cada paciente crea: 1 historia clínica dental (con datos dummy completos) y 1 odontograma (dientes FDI inicializados + diagnóstico/pronóstico/observaciones dummy).

## Estructura que rellenan los datos dummy

### Paciente

- `registro`, `nombre`, `apellidoPat`, `apellidoMat`, `telefono`, `email`, `fechaNacimiento`, `edad`, `genero`, `domicilio`, `motivo_consulta`, `alergias`, `tipo_paciente` (general/ortodoncia/endodoncia), `user_id` (doctor), `clinica_id`, `sucursal_id`, `color`.

### Historia clínica dental

- `paciente_id`, `sucursal_id`, `user_id`, `fecha`, `nombre_doctor`, `alergias`, `motivo_consulta`, `historia_enfermedad`, `veces_cepilla_dia`, `higienizacion_metodo`, antecedentes (af_*, ip_*), antecedentes odontológicos (ao_*), signos vitales (sv_*), examen tejidos blandos (etb_*). El resto de campos opcionales se dejan por defecto o null.

### Odontograma

- `paciente_id`, `sucursal_id`, `historia_clinica_dental_id`, `fecha`, `dientes` (JSON con numeración FDI, estado “sano”), `diagnostico`, `pronostico`, `observaciones`.

El archivo `database/sql/dummy_dental_bulk_reference.sql` (si existe) es solo **referencia** de columnas; no ejecutes esos INSERT en un entorno con encriptación activa.
