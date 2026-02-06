# Resumen técnico – Sistema de Gestión de Pacientes (CERCAP)

Documento para consultor de estrategia. Análisis del repositorio (backend Laravel + frontend React).

---

## 1. Arquitectura general

| Aspecto | Descripción |
|--------|-------------|
| **Estilo** | **Monolito** (una aplicación backend, una SPA frontend). No hay microservicios ni APIs desacopladas por dominio. |
| **Backend** | **Laravel 9** (PHP 8+). API REST vía `routes/api.php`. Dependencias: Laravel Sanctum (auth API), DomPDF/mPDF (PDF), Guzzle, Eluceo/ICAL (calendario), OpenAI PHP (opcional). |
| **Frontend** | **React 18** con **Vite**. Tailwind CSS, React Router 6, Axios, SWR (datos), React Toastify, Chart.js. Single Page Application (SPA) que consume la API. |
| **Base de datos** | **MySQL** por defecto (configurable vía `.env`: `DB_CONNECTION`). Una sola conexión; no hay múltiples BBDD por entorno en código. |

**Comunicación:** Frontend (origen independiente, ej. `localhost:5173`) llama a la API (ej. `APP_URL`) con token Bearer (Sanctum). CORS configurado en backend (`config/cors.php`).

---

## 2. Modelo de tenencia (multitenancy)

- **Enfoque:** **Tenencia por fila (row-level)** en **base de datos compartida**.
  - **No** hay una base de datos por cliente.
  - **No** hay esquemas separados por tenant (solo un schema MySQL).
  - **Sí** se usa una columna **`clinica_id`** (y en muchos casos **`sucursal_id`**) en las tablas de negocio.

- **Entidades clave:**
  - **Clinica:** tenant (empresa/clínica). Campos: `nombre`, `tipo_clinica`, `modulos_habilitados`, `plan`, `max_sucursales`, `max_usuarios`, `max_pacientes`, `activa`, `pagado`, `fecha_vencimiento`, etc.
  - **Sucursal:** pertenece a una clínica (`clinica_id`). La clínica puede tener varias sucursales según plan.
  - **User:** tiene `clinica_id` y `sucursal_id`. Todo usuario pertenece a una clínica.
  - **Paciente, Cita, Evento, Receta, y la mayoría de expedientes/reportes** tienen `clinica_id` (y muchas también `sucursal_id`).

- **Aplicación del tenant:**
  - **Middleware `multi.tenant`:** en cada request autenticado comprueba que el usuario tenga `clinica_id`, que la clínica exista, esté activa y con suscripción vigente. Añade `current_clinica` al request.
  - **Controladores:** filtran por `clinica_id` (y a menudo `sucursal_id`) del usuario autenticado (ej. listado de pacientes por clínica/sucursal, creación con `clinica_id`/`sucursal_id` del usuario).

- **Resumen:** Una sola BD; aislamiento por **`clinica_id`** (y sucursal cuando aplica). Escalado vertical de BD; para escalado horizontal o aislamiento fuerte por cliente habría que valorar esquemas separados o BBDD por tenant en el futuro.

---

## 3. Módulos core e interacción

Funcionalidades principales y cómo se relacionan:

| Módulo | Descripción | Interacción |
|--------|-------------|-------------|
| **Auth** | Login, registro, verificación email, recuperación contraseña. Sanctum tokens. | Base para todo; el middleware multi-tenant depende del usuario y su `clinica_id`. |
| **Gestión de clínicas** | Alta de clínica (registro público con pago), tipos de clínica, módulos habilitados, límites por plan, logo. | Crea el tenant y el primer usuario admin; define qué módulos ve cada clínica. |
| **Sucursales** | CRUD sucursales por clínica, selector de sucursal activa, estadísticas. | Filtrado de pacientes y datos por `sucursal_id`; usuario puede tener sucursal por defecto. |
| **Pacientes** | CRUD pacientes, asociados a `user_id` (médico responsable), `clinica_id`, `sucursal_id`. Tipos: cardíaco, pulmonar, fisioterapia, ambos. | Eje central: expedientes, citas, recetas y reportes pertenecen a un paciente. |
| **Expediente unificado** | Vista agregada de todos los expedientes de un paciente (por tipo). | Consume varios recursos (clinico, esfuerzo, estratificación, reporte final, nutri, psico, fisio, pulmonar, dental, etc.). |
| **Expedientes cardíacos** | Clinico, Esfuerzo, Estratificación, Reporte Final. | Por paciente; generación de PDF e impresión. |
| **Expedientes pulmonares** | ExpedientePulmonar, ReporteFinalPulmonar, NotaSeguimientoPulmonar, PruebaEsfuerzoPulmonar. | Por paciente; PDFs y comparativas. |
| **Fisioterapia** | Historia clínica, nota evolución, nota de alta. | Por paciente; PDFs específicos. |
| **Nutrición / Psicología** | ReporteNutri, ReportePsico. | Por paciente; PDFs. |
| **Cualidades físicas** | CualidadFisica (no aeróbicas). | Por paciente; impresión/descarga. |
| **Dental** | HistoriaClinicaDental, Odontograma. | Por paciente; PDFs. |
| **Recetas** | Receta y RecetaMedicamento, configuración de PDF por clínica. | Por paciente; generación e impresión de receta en PDF. |
| **Citas** | Calendario, estados, envío de invitaciones por email. | Por clínica; asociadas a pacientes. |
| **Eventos** | Recordatorios/tareas por clínica. | Calendario compartido. |
| **Usuarios y permisos** | Admin: crear/editar usuarios de la clínica, asignar permisos (UserPermission, polymorphic). | Usuarios siempre con `clinica_id`; permisos granulares sobre pacientes/expedientes. |
| **PDF y envío** | Generación de PDF (DomPDF/mPDF) para todos los tipos de expediente y recetas; envío por email. | PDFController; firma digital según rol del usuario autenticado (no selección manual de “quién firma”). |
| **IA** | Transcripción, autocompletado, resumen, chat (asistente), acciones. Límites por usuario. | Servicios opcionales sobre textos/expedientes; no bloquean el flujo core. |
| **Dashboard / Analytics** | Estadísticas e insights (incl. IA). | Lectura sobre datos ya acotados por clínica/sucursal. |

**Flujo de datos típico:** Usuario (con `clinica_id`/`sucursal_id`) → lista pacientes de su clínica/sucursal → abre paciente → ve/crea expedientes, recetas, citas. Todo se filtra por tenant y, donde aplica, por sucursal.

---

## 4. Puntos de extensibilidad y adaptación a otros sectores

- **Qué es genérico:**
  - Multitenancy por `clinica_id` (conceptualmente podría ser `organization_id` o `company_id`).
  - Auth, roles, permisos por recurso (polimórfico).
  - Estructura de módulos por “tipo de clínica” y “módulos habilitados” en configuración (`config/clinica_tipos.php`).

- **Qué está muy ligado a salud:**
  - Modelos de dominio: Paciente (datos clínicos), tipos de expediente (cardíaco, pulmonar, fisio, nutri, psico, dental), recetas médicas, citas médicas.
  - Nombres y flujos en controladores, rutas y frontend (expedientes, reportes, recetas, pruebas de esfuerzo, etc.).
  - Configuración de “tipos de clínica” y “módulos” orientada a salud (rehabilitación, dental, nutrición, psicología, etc.).

- **Adaptación a Legal o Logística:**
  - **Arquitectura y tenencia:** Reutilizable (misma BD compartida + `clinica_id`/`organization_id` y middleware de tenant).
  - **Dominio:** Implicaría reemplazar o ampliar fuertemente: entidades (ej. Cliente/Caso vs Paciente, Documento legal vs Expediente), tipos de documento, flujos de aprobación, reportes y PDFs. No es un “cambio de configuración”; requiere nuevo dominio y pantallas.
  - **Conclusión:** La base (monolito, multitenancy por fila, auth, permisos) es extensible; el modelo de dominio actual es específico de salud. Llevar el mismo producto a Legal o Logística sería un desarrollo de dominio y UX considerable sobre esa base, no una adaptación solo por configuración.

---

## 5. Infraestructura y seguridad

| Tema | Implementación |
|------|----------------|
| **Sesiones / tokens** | **Laravel Sanctum:** API con tokens Bearer (personal access tokens). No hay sesión web clásica para la SPA; la SPA envía `Authorization: Bearer <token>`. `sanctum.php`: tokens sin expiración por defecto (`expiration => null`); dominios stateful configurables para cookies si se usaran. |
| **Cifrado de datos** | Contraseñas con **bcrypt** (`config/hashing.php`). No hay cifrado explícito de datos sensibles en reposo (ej. campos en BD) más allá de lo estándar de Laravel (`APP_KEY` para cifrado de valores en app). |
| **HTTPS / entorno** | No hay configuración específica de HTTPS en el repo; depende del entorno (proxy inverso, load balancer). Variables de entorno para `APP_URL`, `FRONTEND_URL`, etc. |
| **CORS** | Configurado en `config/cors.php` para que el frontend (dominio permitido) pueda llamar a la API. |
| **Despliegue** | No hay Dockerfile ni docker-compose en el repositorio. Despliegue típico: servidor PHP (Apache/Nginx) + MySQL + cola/cron si se usan. Frontend: build estático (Vite) servido por el mismo servidor o por un CDN/otro host. Variables críticas: `APP_KEY`, `DB_*`, `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, configuración de mail y, si se usa, claves de IA. |

Recomendaciones típicas para producción: HTTPS obligatorio, revisar expiración de tokens Sanctum, políticas de backup de BD y de almacenamiento de archivos (logos, firmas, PDFs).

---

## 6. Flujo de onboarding

- **Onboarding principal: registro de clínica (nuevo tenant)**  
  1. Usuario anónimo entra a **Registro de clínica** (frontend: `/clinic-registration`).  
  2. Completa datos de la **clínica** (nombre, tipo, módulos, email, teléfono, dirección, plan, duración, pago, transaction_id, etc.) y del **administrador** (nombre, apellidos, email, contraseña, cedula, rol).  
  3. Backend: **POST `/api/clinicas`** (público).  
  4. Se crea: **Clinica** (con límites según plan), **Sucursal** “Principal” de esa clínica, **User** administrador con `isAdmin`/`isSuperAdmin`, `clinica_id` y `sucursal_id` asignados, `email_verified => true`.  
  5. Respuesta exitosa indica que puede iniciar sesión con el email del admin. No se devuelve token en este flujo; el admin debe hacer **login** (POST `/api/login`) para obtener token.  
  6. Tras login, el middleware `multi.tenant` permite acceso a todas las rutas protegidas; el usuario solo ve datos de su clínica/sucursal.

- **Registro de usuario individual (Signup)**  
  - **POST `/api/registro`**: crea un **User** con datos personales y rol; **no** se asigna `clinica_id`. Incluye envío de correo de verificación y generación de token.  
  - Uso típico: usuarios invitados que luego un admin asigna a una clínica, o flujo legacy. Para que el usuario use el sistema “completo” debe tener `clinica_id` (p. ej. asignado por un admin desde la gestión de usuarios).

- **Resumen:** El flujo que deja el sistema listo para usar es **registro de clínica → login del admin**. A partir de ahí, el admin puede crear más usuarios (con `clinica_id`), pacientes, expedientes, citas y recetas dentro de su tenant.

---

*Documento generado a partir del análisis del código (backend: Laravel en `pacientesrhc`, frontend: React en `pacientesrhc-react`). Fecha de referencia: enero 2026.*
