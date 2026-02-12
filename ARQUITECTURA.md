# Arquitectura de la aplicación Pacientes RHC

Documento de análisis de la arquitectura completa: backend (Laravel), frontend (React), funcionalidades por tipo de clínica y flujos de datos.

---

## 1. Visión general

La aplicación es un **sistema de gestión clínica multi-tenant** que soporta varios tipos de clínicas (rehabilitación cardiopulmonar, fisioterapia, dental, nutrición, psicología, general). Cada clínica puede tener varias sucursales, usuarios con roles y permisos, pacientes y expedientes según los módulos habilitados.

| Capa | Tecnología | Ubicación |
|------|------------|-----------|
| Backend API | Laravel (PHP) | `pacientesrhc/` |
| Frontend SPA | React (Vite) | `pacientesrhc-react/` |
| Autenticación | Laravel Sanctum (token) | API + React |
| Base de datos | MySQL (implícito por migraciones) | Laravel |

---

## 2. Backend (Laravel)

### 2.1 Estructura principal

```
app/
├── Console/Commands/     # Comandos Artisan (demos, insights)
├── Exceptions/
├── Helpers/
├── Http/
│   ├── Controllers/      # API y lógica de negocio
│   ├── Middleware/       # auth:sanctum, multi.tenant, etc.
│   ├── Requests/
│   └── Resources/        # API Resources (transformaciones)
├── Mail/
├── Models/               # Eloquent (entidades)
├── Notifications/
├── Providers/
└── Services/             # AIService, CalendarService
config/
├── clinica_tipos.php     # Tipos de clínica y módulos
├── gemini.php            # IA (Gemini)
├── sanctum.php
└── ...
routes/
├── api.php               # Rutas API (Sanctum, multi-tenant)
└── web.php               # Rutas web (welcome, radiografía pública, clinicas)
```

### 2.2 Middleware

- **auth:sanctum**: Protege rutas API; el frontend envía el token en cabecera.
- **multi.tenant**: Filtra datos por `clinica_id` / `sucursal_id` del usuario.
- **EncryptCookies, VerifyCsrfToken**: Grupo `web`.
- **throttle:120,1**: Límite de peticiones en API.

### 2.3 Autenticación y usuarios

- **Login**: `POST /api/login` → token Sanctum.
- **Registro**: `POST /api/registro` (signup usuario).
- **Registro de clínica**: `POST /api/clinicas` (público); `GET /api/clinicas/tipos`.
- **Recuperación de contraseña**: `POST /api/forgot-password`, `POST /api/reset-password/{token}`.
- **Verificación de email**: `GET /api/verify-email/{token}`.
- **Perfil**: `GET/PUT /api/profile/{id}`, subida de imagen, firma, logo universidad.

Cada usuario tiene: `clinica_id`, `sucursal_id`, `rol`, `isAdmin`, `isSuperAdmin`. Los roles se definen en `config/roles.php` (doctor, doctora, fisioterapeuta, recepcionista, administrativo, etc.).

### 2.4 Modelos (entidades)

| Modelo | Descripción |
|--------|-------------|
| **Clinica** | Tipo de clínica, módulos habilitados, plan, límites (sucursales, usuarios, pacientes), logo, estado. |
| **Sucursal** | Pertenece a una clínica; código, dirección, ciudad, es_principal, activa. |
| **User** | Usuario del sistema; pertenece a clínica y sucursal; rol, permisos (UserPermission). |
| **Paciente** | Datos personales (muchos campos encriptados); tipo_paciente (cardiaca, pulmonar, ambos, fisioterapia, general, ortodoncia, etc.); clinica_id, sucursal_id, user_id (médico asignado). |
| **Cita** | fecha, hora, paciente, user (doctor), admin_id, clinica_id, sucursal_id, estado (pendiente, confirmada, completada, cancelada). |
| **Evento** | Eventos/recordatorios del calendario (clinica, sucursal). |
| **Pago** | monto, método (efectivo, tarjeta, transferencia), concepto, cita_id opcional; cifrado en campos sensibles. |
| **Receta** | folio, paciente, user, sucursal, clinica, fecha, diagnostico_principal, indicaciones_generales (encrypted). |
| **RecetaMedicamento** | receta_id, medicamento, presentacion, dosis, frecuencia, duracion, orden. |
| **Clinico** | Expediente clínico (paciente, user, fecha, contenido encrypted). |
| **Esfuerzo** | Prueba de esfuerzo cardíaca (paciente, user, fecha, contenido). |
| **Estratificacion** | Estratificación de riesgo (paciente, user, fecha, contenido). |
| **ReporteFinal** | Reporte final cardíaco. |
| **ReporteNutri** | Nota nutricional. |
| **ReportePsico** | Nota psicológica. |
| **ReporteFisio** | Reporte fisioterapia (contenido). |
| **HistoriaClinicaFisioterapia** | Historia clínica fisio (motivo, antecedentes, diagnóstico, etc.). |
| **NotaEvolucionFisioterapia** | Nota de evolución fisio. |
| **NotaAltaFisioterapia** | Nota de alta fisio. |
| **ExpedientePulmonar** | Expediente clínico pulmonar. |
| **ReporteFinalPulmonar** | Reporte final pulmonar. |
| **PruebaEsfuerzoPulmonar** | Prueba de esfuerzo pulmonar. |
| **NotaSeguimientoPulmonar** | Nota SOAP seguimiento pulmonar. |
| **CualidadFisica** | Cualidades físicas no aeróbicas. |
| **HistoriaClinicaDental** | Historia clínica dental (odontología). |
| **Odontograma** | Odontograma (dientes FDI, análisis periodontal/endodóntico); relación con HistoriaClinicaDental. |
| **RadiografiaDental** | Imágenes/radiografías dentales. |
| **UserPermission** | Permisos granulares (morph: permissionable). |
| **AuditLog** | Registro de auditoría (clinica, sucursal). |

### 2.5 Tipos de clínica y módulos (config)

Definidos en `config/clinica_tipos.php`:

- **rehabilitacion_cardiopulmonar**: Cardíaca, pulmonar y fisioterapia (expediente_cardiaco, expediente_pulmonar, expediente_fisioterapia, prueba_esfuerzo, estratificacion, reporte_final, reporte_nutri, reporte_psico).
- **dental**: expediente_dental, odontograma, tratamientos, presupuestos, imagenologia.
- **fisioterapia**: expediente_fisio, sesiones, ejercicios, evaluaciones.
- **nutricion**, **psicologia**, **general**: con sus propios módulos.

### 2.6 Rutas API (resumen por dominio)

- **Usuario y perfil**: `/user`, `/logout`, `/profile/{id}`, subida imagen/firma.
- **Pacientes**: CRUD `apiResource /pacientes`.
- **Citas**: CRUD, `getCalendarData`, `changeStatus`, `storeMultiple`, `forceDelete`.
- **Eventos**: CRUD, `getCalendarData`.
- **Expedientes cardíacos/rehab**: `/esfuerzo`, `/estratificacion`, `/clinico`, `/reporte`, `/psico`, `/nutri` (apiResource); eliminación por rutas específicas (`/esfu/{id}`, etc.).
- **Dental**: `/historia-dental`, `/odontogramas`, por paciente y latest; `/radiografias-dentales`; impresión PDF.
- **Recetas**: index, por paciente, store, show, imprimir PDF, config PDF.
- **Finanzas**: `/finanzas/pagos`, historial por paciente, recibo PDF y envío email, corte de caja, corte por período, estadísticas.
- **Expedientes unificado**: `GET /expedientes/{pacienteId}`.
- **Sucursales**: apiResource, `getByClinica`, `cambiarSucursal`, `estadisticas`.
- **Fisioterapia**: `/fisioterapia/historia`, `/fisioterapia/evolucion`, `/fisioterapia/alta` (CRUD cada uno); `/fis` store; imprimir por tipo.
- **Pulmonar**: `/pulmonar/*` (expediente, impresión).
- **Cualidades físicas**: `/cualidades-fisicas/*` (CRUD, print, download).
- **Reporte final pulmonar**: CRUD, generar desde comparación, print, download.
- **Nota seguimiento pulmonar**: CRUD.
- **Prueba esfuerzo pulmonar**: CRUD, por paciente, print, download.
- **IA**: `/ai/transcribe`, `/ai/autocomplete`, `/ai/summarize`, `/ai/chat`, `/ai/context`, `/ai/action`.
- **Dashboard**: `/dashboard/insights`, `/dashboard/stats`, `/dashboard/alerts`.
- **Admin**: `/admin/users`, permisos (assign, revoke, bulk), `getUserPermissions`, `getMyResourcesWithPermissions`.
- **Analytics**: `GET /analytics` (administradores).
- **Clínicas**: tipos (público), store (público), show, getCurrentClinica, updateCurrentClinica, uploadLogo (autenticado).

Todas las rutas de expedientes tienen rutas de **impresión PDF** (PDFController) y envío por email donde aplica.

---

## 3. Frontend (React)

### 3.1 Estructura

```
src/
├── main.jsx
├── router.jsx              # Rutas (createBrowserRouter)
├── index.css
├── axios-client.js         # Cliente HTTP (baseURL, token)
├── contexts/
│   └── contextProvider.jsx # user, token, sucursalActual, loadUserInfo
├── hooks/
│   ├── useAuth.js
│   ├── useClinica.js
│   ├── useClinicaPermisos.js  # Menú y permisos por tipo clínica
│   ├── useExpedienteActions.js
│   ├── useExpedientes.js
│   ├── usePacienteData.js
│   ├── useUserLoader.js
│   └── ...
├── core/api/               # client.js, index (API base)
├── config/
│   ├── clinicaTypes.js     # Tipos paciente (cardiaca, pulmonar, fisio)
│   ├── expedienteActions.js # Acciones por tipo expediente
│   └── soporte.js
├── utils/
│   ├── expedienteConfig.js # TIPOS_EXPEDIENTE (id, nombre, ruta, componente)
│   ├── constants.js
│   ├── cacheUtils.js
│   ├── imageUtils.js
│   └── helpers.js
├── components/
│   ├── layout/             # DefaultLayout, GuestLayout, Header, Sidebar
│   ├── Forms/              # Formularios por expediente (FormClinico, FormPrueba, etc.)
│   ├── Modals/             # Confirmación, CualidadesFisicas, PruebaEsfuerzoPulmonar, etc.
│   ├── AI/                 # AITextarea, SmartTextarea, VoiceTranscription
│   ├── Recetas/            # FormReceta
│   ├── Paciente/           # ExpedienteActionsRefactored
│   ├── SucursalSelector.jsx
│   ├── SucursalesManager.jsx
│   ├── ModalExpediente.jsx
│   ├── ModalRegistrarPago.jsx
│   ├── ModalCorteFinanzas.jsx
│   └── ...
├── views/
│   ├── auth/               # Login, ForgotPassword, ClinicRegistration
│   ├── Dashboard.jsx       # Listado pacientes (filtros, tipo paciente)
│   ├── Paciente.jsx        # Detalle paciente + expedientes
│   ├── Calendar.jsx        # Calendario citas
│   ├── Caja.jsx            # Finanzas, pagos, corte
│   ├── Recetas.jsx
│   ├── Analytics.jsx       # Dashboard/analíticas
│   ├── Clinica.jsx         # Info clínica + pestaña Sucursales (SucursalesManager)
│   ├── Users.jsx           # Gestión usuarios (admin)
│   ├── Perfil.jsx
│   ├── Compare.jsx / ComparePulmonar.jsx
│   ├── Edit/                # EditClinico, EditPrueba, EditNutri, EditHistoriaFisio, etc.
│   ├── TestIA.jsx
│   └── NotFound.jsx
└── features/
    ├── expedientes/services/
    ├── pacientes/services/
    ├── recetas/services/
    └── sucursales/         # useSucursales, sucursal.service, SucursalesView
```

### 3.2 Rutas (router.jsx)

- **DefaultLayout** (autenticado): `/` → redirect `/dashboard`, `/dashboard` (Analytics), `/pacientes` (Dashboard listado), `/calendar`, `/analytics`, `/recetas`, `/caja`, `/paciente/:id`, `/prueba/:id`, `/estrati/:id`, `/clinico/:id`, `/perfil/:id`, `/compare/:id`, `/compare-pulmonar/:id`, `/nutri/:id`, `/psico/:id`, `/pulmonar/:id`, `/historia/:id`, `/evolucion/:id`, `/alta/:id`, `/users`, `/clinica` (ProtectedSuperAdminRoute), `/test-ia`.
- **GuestLayout**: `/login`, `/forgot-password`, `/reset-password/:token`, `/verify-email/:token`, `/clinic-registration`.
- **404**: `*` → NotFound.

La gestión de **sucursales** se hace dentro de **Clinica** (pestaña “Sucursales”) con `SucursalesManager`; no hay ruta dedicada `/sucursales` en el router.

### 3.3 Estado global y autenticación

- **ContextProvider**: `user`, `token` (localStorage `AUTH_TOKEN`), `sucursalActual` (localStorage `SUCURSAL_ACTUAL`), `loadUserInfo`, `clearUserInfo`, `setSucursalActual`.
- **useAuth**: logout, user (desde contexto).
- **useClinica**: datos de la clínica actual.
- **useClinicaPermisos**: menú y acceso por módulo según `tipo_clinica` y rol (incluye enlace a “Clínica” y “Sucursales” para super admin).

El **Sidebar** muestra u oculta ítems según rol (admin, super admin) y tipo de clínica (ej. “Usuarios” oculto en dental si no es super admin). Incluye: Inicio, Pacientes, Usuarios (condicional), Recetas, Caja, Calendario, Clínica (condicional).

### 3.4 Expedientes (configuración)

En **expedienteConfig.js** se definen los tipos de expediente: id, nombre, descripción, icono, color, componente (form), ruta de edición, ruta de impresión, `tipo_exp`. Incluye: prueba de esfuerzo, estratificación, clínico, psico, nutri, fisio, pulmonar, historia/evolución/alta fisio, cualidades físicas, reporte final pulmonar, prueba esfuerzo pulmonar, historia dental, odontograma, nota seguimiento pulmonar.

**ModalExpediente** y **Paciente** usan esta config para mostrar los expedientes disponibles según `tipo_paciente` del paciente y tipo de clínica.

---

## 4. Funcionalidades por módulo

### 4.1 Gestión de pacientes

- Listado con filtros (nombre, tipo de paciente, sucursal).
- Alta/edición: datos personales, motivo consulta, alergias, tipo_paciente, médico asignado, sucursal.
- Vista detalle: datos del paciente + lista de expedientes (por tipo) con acciones (ver, editar, imprimir, enviar email, eliminar según permisos).

### 4.2 Calendario de citas

- Vista calendario; creación/edición de citas (paciente, doctor, fecha, hora, estado).
- Creación de paciente desde calendario.
- Estados: pendiente, confirmada, completada, cancelada.
- Integración con eventos (recordatorios/tareas).

### 4.3 Recetas

- Listado y filtros; creación de receta (folio, diagnóstico, indicaciones, medicamentos con dosis/frecuencia/duración).
- Impresión PDF; configuración de PDF por clínica.

### 4.4 Caja / Finanzas

- Registro de pagos (monto, método, concepto, cita opcional).
- Historial de pagos por paciente.
- Corte de caja (diario y por período); exportación.
- Estadísticas financieras.
- Recibo en PDF y envío por email.

### 4.5 Expedientes según tipo de clínica

- **Rehabilitación cardiopulmonar**: Clínico, Prueba de esfuerzo, Estratificación, Reporte final, Nutri, Psico; opcional Fisio (reporte, historia, evolución, alta); Pulmonar (expediente, prueba esfuerzo, reporte final, nota seguimiento); Cualidades físicas. Comparación de expedientes (Compare, ComparePulmonar).
- **Fisioterapia**: Historia clínica fisio, Nota evolución, Nota alta, Reporte fisio.
- **Dental**: Historia clínica dental, Odontograma (FDI), Radiografías dentales.

Cada tipo tiene formularios de edición (vistas Edit/* o modales) e impresión PDF vía API.

### 4.6 IA (asistente y ayudas)

- Transcripción de voz.
- Autocompletado y resumen de texto.
- Chat (asistente médico según tipo de clínica: Dr. CardioBot, FisioBot, DentalBot, etc.).
- Contexto del día y ejecución de acciones.
- Componentes: AITextarea, SmartTextarea, VoiceTranscription, AsistenteMedico.

### 4.7 Dashboard y analíticas

- Dashboard con estadísticas e insights (API `/dashboard/insights`, `/dashboard/stats`, `/dashboard/alerts`).
- Analytics para administradores (`/analytics`).

### 4.8 Clínica y sucursales

- **Clínica** (vista `/clinica`, super admin): datos de la clínica, logo, pestaña Sucursales con **SucursalesManager** (listar, crear, editar, eliminar sucursales).
- **SucursalSelector**: cambio de sucursal activa (multi-sucursal); sucursal actual en contexto.

### 4.9 Usuarios y permisos

- **Users** (admin): listado, crear, editar, eliminar usuarios; asignación de sucursal; permisos (assign/revoke, bulk).
- Permisos granulares por recurso (paciente, expedientes) vía UserPermission.

### 4.10 Perfil

- Edición de datos de usuario; foto de perfil; firma digital; logo universidad (según rol).

### 4.11 Otros

- **Eventos/recordatorios**: calendario de eventos.
- **Alertas eventos**: componente de alertas.
- **Ayuda y soporte**: AyudaSoporte, configuración en `config/soporte.js`.
- **Demostraciones**: comandos `demo:create-dental`, `demo:completar-dental`, `demo:cargar-sucursales-dental`, `demo:create-rehab` (cardiopulmonar-fisio, cardiopulmonar, fisioterapia). Ver DEMO_DENTAL.md y DEMO_CLINICAS.md.

---

## 5. Seguridad y multi-tenant

- Toda la API protegida por **Sanctum**; el frontend envía `Authorization: Bearer {token}`.
- Middleware **multi.tenant** asegura que las consultas estén acotadas a la clínica/sucursal del usuario.
- Datos sensibles en backend: **encriptación** con `APP_KEY` (Paciente, expedientes, Pago, Receta, etc.). Los demos deben crearse en el mismo entorno donde se usan.
- Permisos por recurso (expedientes, pacientes) vía **UserPermission** y comprobaciones en backend.

---

## 6. Integraciones

- **Correo**: notificaciones (citas, recordatorios, recuperación de contraseña, verificación de email); envío de recibos y expedientes por email.
- **PDF**: Dompdf (o configurado en `config/dompdf.php`) para recetas, reportes, odontogramas, recibos, etc.
- **IA**: Gemini (config `config/gemini.php`); transcripción, autocompletado, resumen, chat, acciones.
- **Calendario**: CalendarService en backend; en frontend, vista Calendar con citas y eventos.

---

## 7. Resumen de flujos

1. **Login**: Frontend envía credenciales → API devuelve token → token se guarda y se usa en todas las peticiones; `loadUserInfo` carga user y sucursal.
2. **Trabajo por sucursal**: Usuario con varias sucursales elige una en SucursalSelector; las peticiones y listados se filtran por esa sucursal (según backend).
3. **Paciente**: Se crea/edita en Dashboard o desde Calendario; se asigna a médico y sucursal; en detalle se gestionan expedientes según tipo_paciente y módulos de la clínica.
4. **Expediente**: Se abre el formulario correspondiente (ruta Edit o modal); se guarda vía API; impresión y envío por email vía endpoints de PDF.
5. **Caja**: Registro de pago (opcionalmente ligado a cita); corte y estadísticas vía `/finanzas/*`.

---

*Documento generado a partir del análisis del código en los proyectos pacientesrhc (Laravel) y pacientesrhc-react (React).*
