# Project map — PsicoCMS

> Fuente de verdad del estado real del sistema. Si hay discrepancia con el código, manda el código.
> El plan de implementación vigente es `plan-implementación.md` (no existe ningún `Plan_de_implementación_CLAUDE.md`).

## Estado de fases
| Fase | Estado |
|---|---|
| 0 — Preparación y base de datos | ✅ Completada (ejecutada como dependencia directa de la Fase 1) · pendiente de validación del usuario |
| 1 — Asistente de instalación | ✅ Completada · pendiente de validación del usuario |
| 2 — Login seguro | ✅ Completada · pendiente de validación del usuario |
| 3 — Layout y menú del panel | ✅ Completada · pendiente de validación del usuario |
| 4 — Inicio, disponibilidad y gestión de citas | ✅ Completada · pendiente de validación del usuario |
| 5 — Calendario | ✅ Completada · pendiente de validación del usuario |
| 6–18 | Pendientes |

Validación: no se han ejecutado tests ni se ha probado la aplicación en el navegador (por indicación del usuario). Solo comprobaciones estáticas: `php -l`, `php artisan route:list` y `php artisan view:cache` sin errores.

## Entorno
- PHP 8.2.12 (XAMPP) con `gd`, `zip` e `intl` activadas en `D:\xampp\php\php.ini` (copia previa: `D:\xampp\php\php.ini.bak-psicocms`).
- Composer 2.10.2 · MariaDB 10.4.32 (XAMPP; debe estar arrancado para el instalador).
- Laravel 12.69.3 en `psicocms/`. Servidor: `cd psicocms && php artisan serve` → http://127.0.0.1:8000
- Paquetes: `barryvdh/laravel-dompdf` ^3.1, `mews/purifier` ^3.4. Sin Vite ni npm.
- Librerías vendorizadas en `psicocms/public/vendor/`: Font Awesome 6.1.2 (`fontawesome/`), Jodit 4.17.1 build fat (`jodit/`), FullCalendar 6.1.21 + locale `es` (`fullcalendar/`).
- Fuentes del panel en `public/panel/fonts/`: Manrope variable (woff2 latin + latin-ext) y Lexend 400/500/600 como respaldo.

## Configuración
- `.env.example` y `.env`: `APP_NAME=PsicoCMS`, `APP_LOCALE=es`, `APP_TIMEZONE=Europe/Madrid`, `DB_CONNECTION=mysql` (BD `psicocms`, root sin contraseña por defecto), `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`.
- Valores por defecto equivalentes en `config/app.php`, `cache.php`, `database.php`, `queue.php`, `session.php`.
- `config/psicocms.php`: ruta de `installed.lock`, requisitos del instalador, 8 paletas del panel, módulos activables, slots de imágenes, parámetros de reserva (2 h de antelación, 90 días), duraciones rápidas (30/45/50/60), moneda (`currency`: COP, símbolo `$`, sin decimales, miles con punto, precio máximo 999.999), ajustes cifrados (`mail.app_password`), tema por defecto `calma`, modos de tema.
- `config/phrases.php`: vacío (se rellena en la Fase 15).
- `config/panel.php`: `menu` (estructura del sidebar: accesos directos y grupos Blog / Mi web / Configuración, con patrón `match` para el elemento activo) y `coming_soon` (secciones aún no implementadas: URI, título, icono y texto). Cada fase debe **quitar su entrada de `coming_soon`** al crear la ruta real con el mismo nombre.
- `AppServiceProvider`: `Route::resourceVerbs(['create' => 'crear', 'edit' => 'editar'])` para URLs en español.
- `config/purifier.php`: añadido el perfil `jodit` (etiquetas y estilos permitidos para el editor).
- Idioma: `lang/es/{validation,auth,pagination,passwords}.php` y `lang/es.json` (páginas de error de Laravel).
- `composer.json`: autoload de `app/Support/helpers.php`; scripts `setup` (install + `.env` + key + `storage:link`) y `dev` (`php artisan serve`).
- `public/storage` enlazado a `storage/app/public` (`php artisan storage:link`).

## Base de datos (migraciones en `psicocms/database/migrations`)
| Tabla | Migración |
|---|---|
| `users` (first_name, last_name, email único, phone único, password, avatar_path, panel_mode, panel_color, remember_token), `password_reset_tokens`, `sessions` | `0001_01_01_000000_create_users_table` |
| `cache`, `jobs` (esqueleto Laravel, sin uso con drivers file/sync) | `0001_01_01_000001/2` |
| `settings` (key único, value longText JSON) | `2026_10_04_000100` |
| `availability_settings` (modality única, session_duration, break_enabled, break_minutes, day_start, day_end, needs_review), `availability_slots` (modality, weekday 1–7, start_time; único), `vacation_periods` | `2026_10_04_000200` |
| `patients` (phone único normalizado + datos personales/clínicos, status, source, privacy_signed_at, softDeletes) | `2026_10_04_000300` |
| `appointments` (patient_id FK, modality, starts_at, ends_at, break_minutes, status, source, reason, internal_notes, price, public_token uuid, seen_at) | `2026_10_04_000400` |
| `clinical_entries` (softDeletes), `clinical_attachments` | `2026_10_04_000500` |
| `services`, `specialties`, `plans`, `faqs`, `site_images`, `phrases` | `2026_10_04_000600` |
| `blog_categories`, `blog_posts` | `2026_10_04_000700` |

## Modelos (`app/Models`)
`User`, `Setting`, `AvailabilitySetting` (`step()`, `effectiveBreak()`, `forModality()`), `AvailabilitySlot`, `VacationPeriod` (scopes `covering`, `overlapping`, `upcoming`), `Patient` (mutator de teléfono, `full_name`, `initials`, scope `active`), `Appointment` (constantes de modalidad/estado/origen, `public_token` automático, scopes `notCancelled`, `upcoming`, `onDate`), `ClinicalEntry`, `ClinicalAttachment`, `Service`, `Specialty`, `Plan` (`featureList()`, scope `forModality`), `Faq`, `BlogCategory`, `BlogPost` (scope `published`), `SiteImage`, `Phrase`. Trait `Concerns\Sortable` (scopes `active`, `ordered`).
Factories para User, Patient, Appointment, ClinicalEntry, BlogCategory, BlogPost, Service, Specialty, Plan y Faq.

## Seeders
- `BaseDataSeeder` (idempotente, lo ejecuta el instalador): 6 categorías de blog, `availability_settings` de ambas modalidades, todos los módulos activados, `theme.active=calma`, `theme.mode=landing`, `booking.vacation_mode=false`, `mail.enabled=false`, plantilla RGPD por defecto (`privacy.template`).
- `DemoDataSeeder` (opcional desde el instalador): servicios, especialidades, planes y FAQs (solo si las tablas están vacías), 3 artículos con `database/seeders/demo/blog1-3.jpg`, 14 pacientes, citas de las últimas 8 semanas y las 2 próximas sin solapes, historias clínicas. Usa Faker (dependencia de desarrollo).
- `DatabaseSeeder` = Base + Demo.

## Servicios y soporte
| Clase | Estado |
|---|---|
| `Support\Phone` | `normalize()` e `isValid()` (patrón `^\+?\d{6,15}$`) |
| `Support\EnvWriter` | Lee/escribe claves del `.env` con entrecomillado seguro |
| `Support\Installation` | `isInstalled()`, `markInstalled()` sobre `storage/app/installed.lock` |
| `Support/helpers.php` | `setting()`, `phrase()`, `site_image()`, `theme_asset()`, `theme_image()`, `public_storage_url()`, `money($importe, $conCodigo = false)` → “$ 150.000” (formato desde `config('psicocms.currency')`) |
| `Services\SettingsService` | get/set/many/setIfMissing/group/forget con caché `rememberForever`, JSON y cifrado de claves sensibles; tolera BD inexistente |
| `Services\ThemeManager` | **Parcial**: descubre temas por `theme.json`, `find()`, `exists()`, `activeSlug()`, `activeMode()`. Namespace de vistas, assets y previsualización quedan para la Fase 12 |
| `Services\AvailabilityService` | **Fuente única de disponibilidad** (panel y, en la Fase 17, web pública): `times()` (algoritmo de huecos), `grid()`, `weeklySlots()`, `weeklyRanges()`, `fillWeekdays()`, `saveWeeklySlots()` (descarta horas fuera de la rejilla y limpia `needs_review` de esa modalidad), `updateSchedule()` (si cambia duración/descanso/horas: poda los huecos que ya no encajan y marca `needs_review` en **ambas** modalidades), `modalitiesNeedingReview()`, `isVacationMode()`/`setVacationMode()`, `blockReason()`, `slotsForDate($modality, $date, $ignoreId, $forPanel)`, `availableDates()`, `isBookable()`, `overlaps()`, `bookingHorizon()` |
| `Services\PatientService` | `findOrCreateByPhone($data, $source)`: normaliza el teléfono, restaura pacientes borrados, solo rellena campos vacíos (nombre, apellidos, email, motivo, modalidad preferida) sin sobrescribir |
| `Services\AppointmentService` | `create()`, `update()`, `changeStatus()`, `move()` (mantiene la duración; rechaza canceladas, fechas pasadas y solapes; no exige que el destino esté en la rejilla), `delete()`, `defaultPrice()`; todo en `DB::transaction` con `lockForUpdate` de las citas del día, comprobación de solapes (`slot_time`: “Ese horario se solapa con la cita de X a las HH:MM.”) y, si no es hora personalizada y cambia el horario, comprobación de que el hueco está libre en la disponibilidad; `ends_at` = inicio + duración vigente y `break_minutes` guardado como copia del descanso vigente |
| `Services\CalendarService` | `events($from, $to)` (citas del rango con `extendedProps` —modalidad, estado, teléfono, email, motivo, precio, duración, descanso, etiquetas de fecha/hora y URLs de editar, estado, mover, borrar y paciente— y periodos de vacaciones como eventos de fondo), `appointmentEvent()`, `businessHours()` (rangos de la rejilla de ambas modalidades en formato FullCalendar, domingo = 0), `scrollTime()` |
| `Services\DashboardStatsService` | `summary()` (citas de hoy, pacientes activos, artículos publicados, ingresos del mes = suma de `price` de citas no canceladas del mes), `todayAppointments()`, `newWebBookings()` (origen web y `seen_at` nulo), `weeklyAvailability()`, `lastWeeksActivity()` (8 semanas) |
| `Services\HtmlSanitizer` | Purifier con perfil `jodit` |
| `Services\ImageUploader` | Guarda en disco `public` (`uploads/{carpeta}`) con nombre aleatorio y borra la anterior |
| `Services\Installer\RequirementsChecker` | PHP, extensiones, permisos de `storage/`, `bootstrap/cache/` y `.env` |
| `Services\Installer\DatabaseInstaller` | Crea la BD (utf8mb4_unicode_ci), configura la conexión en caliente, vacía la caché, migra, ejecuta `BaseDataSeeder` y escribe `.env` (solo si cambia); mensajes de error amables |
| `Services\Installer\InstallerProgress` | Pasos del asistente y progreso en sesión |

## Middleware
- `EnsureInstalled` (antepuesto al grupo `web`): sin `installed.lock` redirige todo a `/instalacion` (excepto `instalacion*` y `themes/*`).
- `RedirectIfInstalled` (alias `not.installed`): 404 en el instalador una vez instalado.
- `EnsureInstallerStep` (alias `installer.step`): impide saltarse pasos y exige BD accesible desde el paso “cuenta”; se ejecuta antes de los Form Requests.
- `SecurityHeaders` (añadido al grupo `web`): `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.
- `redirectGuestsTo(route('login'))` → invitados a `/acceso-psicologa`; `redirectUsersTo(route('panel.home'))` → usuarios autenticados que abren el login van al panel.
- Excepciones (`bootstrap/app.php`): un 419 (CSRF caducado) en `login.store` redirige al login con aviso amable en lugar de la página de error.
- `EnvironmentServiceProvider` (primer proveedor): crea `.env` desde `.env.example` y genera `APP_KEY` si faltan (no actúa en tests).

## Rutas
`routes/web.php` incluye `installer.php`, `auth.php`, `panel.php` y `site.php`.

| Método | URL | Nombre | Notas |
|---|---|---|---|
| GET | `/instalacion` | `installer.index` | Redirige al primer paso pendiente |
| GET | `/instalacion/{paso}` | `installer.show` | requisitos, base-de-datos, cuenta, datos-publicos, horarios, foto, tema, finalizar |
| POST | `/instalacion/requisitos` · `/base-de-datos` · `/cuenta` · `/datos-publicos` · `/horarios` · `/foto` · `/tema` · `/finalizar` | `installer.*` | Con `installer.step:{paso}` |
| GET | `/acceso-psicologa` | `login` | `guest`. Formulario de acceso |
| POST | `/acceso-psicologa` | `login.store` | `guest`. Valida email + teléfono + contraseña, throttle |
| POST | `/panel-psicologa/cerrar-sesion` | `logout` | `auth`. Cierra sesión |
| GET | `/panel-psicologa` | `panel.home` | `auth`. `Panel\DashboardController`: inicio con estadísticas reales |
| GET | `/panel-psicologa/disponibilidad` | `panel.availability` | `auth`. Página de disponibilidad (`?tab=online|presencial`) |
| GET | `/panel-psicologa/disponibilidad/huecos?modalidad&fecha&ignorar` | `panel.availability.slots` | `auth`. JSON de huecos libres para el panel (`slots`, `blocked`, `message`) |
| PATCH | `/panel-psicologa/disponibilidad/modo-vacaciones` | `panel.availability.vacation-mode` | `auth`. JSON `{enabled}` |
| PUT | `/panel-psicologa/disponibilidad/{online|presencial}/configuracion` | `panel.availability.schedule` | `auth`. Duración, descanso y horas (formulario) |
| PUT | `/panel-psicologa/disponibilidad/{online|presencial}/huecos-semanales` | `panel.availability.weekly` | `auth`. JSON `{slots: [{weekday, time}]}` |
| POST | `/panel-psicologa/disponibilidad/vacaciones` | `panel.availability.vacations.store` | `auth`. Añade periodo |
| DELETE | `/panel-psicologa/disponibilidad/vacaciones/{period}` | `panel.availability.vacations.destroy` | `auth`. Borra periodo |
| GET/POST | `/panel-psicologa/citas` · `/citas/crear` | `panel.appointments.index/create/store` | `auth`. Listado con filtros y alta |
| GET/PUT/DELETE | `/panel-psicologa/citas/{appointment}/editar` · `/citas/{appointment}` | `panel.appointments.edit/update/destroy` | `auth` (el borrado responde JSON si la petición lo pide) |
| PATCH | `/panel-psicologa/citas/{appointment}/estado` | `panel.appointments.status` | `auth`. Cambio rápido de estado (redirección o JSON si la petición lo pide) |
| PATCH | `/panel-psicologa/citas/{appointment}/mover` | `panel.appointments.move` | `auth`. JSON `{start: "Y-m-d H:i"}` (arrastrar en el calendario) |
| GET | `/panel-psicologa/calendario` | `panel.calendar` | `auth`. Calendario |
| GET | `/panel-psicologa/calendario/eventos?start&end` | `panel.calendar.events` | `auth`. JSON de eventos (máximo 62 días; solo usa la parte de fecha de los parámetros) |
| GET | `/panel-psicologa/perfil` | `panel.profile` | `auth`. Redirige a `/configuracion/general` |
| GET | `/panel-psicologa/` + pacientes, historias, blog/articulos, blog/categorias, mi-web/(datos, sobre-mi, servicios, especialidades, planes, preguntas-frecuentes, imagenes, frases, redes-sociales, temas, seo, textos-legales), configuracion/(general, modulos, email, proteccion-datos), buscar, ayuda | `panel.*` (ver `config/panel.php`) | `auth`. **Provisionales**: `Panel\ComingSoonController` con empty state “Próximamente” |
| GET | `/` | `site.home` | **Provisional**: “web en construcción”; se sustituye en la Fase 16 |

## Asistente de instalación (Fase 1)
- Controlador: `app/Http/Controllers/Installer/InstallerController.php`.
- Form Requests: `app/Http/Requests/Installer/{Database,Account,PublicData,Schedule,Photo,Theme}Request.php`.
- Vistas: `resources/views/installer/layout.blade.php`, `partials/actions.blade.php`, `steps/{requisitos,base-de-datos,base-de-datos-lista,cuenta,datos-publicos,horarios,foto,tema,finalizar}.blade.php`.
- Qué guarda cada paso:
  1. Requisitos → crea `public/storage` si falta.
  2. Base de datos → crea la BD, migra, siembra `BaseDataSeeder` y escribe `DB_*` en `.env`.
  3. Cuenta → crea o actualiza el **único** usuario (la contraseña es opcional si ya existe).
  4. Datos públicos → `settings` `site.*` (nombre público, eslogan, colegiada, teléfono/email de citas, WhatsApp, dirección, ciudad, “sobre mí” saneado); reemplaza `specialties`, `services` y `plans` (online/presencial).
  5. Horarios → `availability_settings` por modalidad y rejilla completa de `availability_slots` para los días elegidos; actualiza `duration_label` de los planes.
  6. Foto (opcional) → `settings.site.photo` (`storage/app/public/uploads/perfil`).
  7. Tema → `theme.active`, `theme.mode` y la opción de contenido de ejemplo.
  8. Finalizar → `DemoDataSeeder` si se pidió, `installed.lock`, inicio de sesión y redirección a `/panel-psicologa` con toast de bienvenida.
- Reanudación: el progreso vive en la sesión (driver file); todos los pasos son idempotentes (sin duplicar usuario ni datos).
- Temas disponibles: `psicocms/themes/{calma,serenidad,salvia,lavanda,esencia}/theme.json` (solo manifiestos: nombre, descripción, versión, orden, `preview` y `images`). Vistas y assets en la Fase 12.

## Autenticación (Fase 2)
- `app/Http/Requests/Auth/LoginRequest.php`: normaliza email (minúsculas) y teléfono (`Phone::normalize`); los 3 campos obligatorios; `authenticate()` busca por email, compara el teléfono con `hash_equals` y la contraseña con `Hash::check` (contra un hash ficticio si el email no existe, para no revelar qué emails existen por tiempo de respuesta); mensaje genérico `auth.failed` en la clave `login`; rehash automático si cambia el coste; `Auth::login($user, $remember)`.
- Límite: 5 intentos fallidos por minuto por email+IP (`RateLimiter`, evento `Lockout`), mensaje `auth.throttle` con los segundos restantes; se limpia al entrar.
- `app/Http/Controllers/Auth/LoginController.php`: `show`, `store` (regenera la sesión, `intended` al panel con toast “¡Hola de nuevo, …!”), `destroy` (logout, invalidación de sesión, regeneración del token CSRF, toast de confirmación).
- “Mantener la sesión iniciada”: cookie *remember me* nativa de Laravel (`remember_token` en `users`). Contraseñas con bcrypt (cast `hashed`, `BCRYPT_ROUNDS=12`).
- Vista `resources/views/auth/login.blade.php` (imagen lateral `public/panel/img/acceso.jpg`, copiada de `tema-visual-base/assets/img/psicologia.jpg`; se oculta en móvil), CSS `public/panel/css/pages/login.css`, JS `public/panel/js/pages/login.js` (toasts, mostrar/ocultar contraseña, estado de carga).

## Layout del panel (Fase 3)
- `resources/views/panel/layout.blade.php`: `<html data-mode data-accent>` con los valores del usuario (`panel_mode`, `panel_color`) para que no haya parpadeo; enlace “Saltar al contenido”; sidebar + overlay + topbar + `<main id="contenido">`; toasts (`x-panel.flash`); `@stack('styles')`, `@stack('scripts')`, `@stack('modules')`; carga `panel/js/pages/panel.js`.
- `panel/partials/sidebar.blade.php`: marca “PsicoCMS · Panel de gestión”, botón “+ Nueva cita”, menú desde `config('panel.menu')` (grupos con `aria-expanded`/`aria-controls`, abiertos si contienen la ruta activa, `aria-current="page"`), pie con Ver mi web (nueva pestaña), Ayuda y Cerrar sesión.
- `panel/partials/topbar.blade.php`: hamburguesa (móvil), buscador (GET a `panel.search`), campana con desplegable (estado vacío; datos reales en Fase 15), botón de apariencia luna/sol (de momento muestra un aviso; selector en Fase 14), Ayuda con tooltip, avatar + nombre con menú (Mi perfil, Ver mi web, Cerrar sesión).
- `panel/home.blade.php` (cabecera + empty state) y `panel/coming-soon.blade.php`.
- `app/Http/Controllers/Panel/ComingSoonController.php`: renderiza la sección de `config('panel.coming_soon')` según el nombre de la ruta.
- Responsive: sidebar fijo ≥1025 px; off-canvas con overlay, Esc y botón de cierre en ≤1024 px; buscador a ancho completo en ≤640 px; Ayuda oculta en ≤360 px.

## Inicio, disponibilidad y citas (Fase 4)
- **Inicio** (`panel/home.blade.php`, `pages/dashboard.css`): saludo, avisos si el modo vacaciones está activo o hay huecos por revisar, 4 tarjetas de estadísticas (enlazadas), tabla “Próximas citas de hoy” (las pasadas atenuadas), gráfico de barras CSS de las últimas 8 semanas, tarjeta “Disponibilidad semanal” (rangos continuos por día y modalidad) y “Nuevas reservas web”; empty states en cada bloque.
- **Disponibilidad** (`Panel\AvailabilityController`, `Panel\VacationPeriodController`, vistas `panel/availability/{index,_schedule-form,_week-grid}.blade.php`, `pages/availability.css`, `pages/availability.js`, `modules/week-grid.js`, `modules/schedule-summary.js`):
  - Fila superior: “Modo vacaciones” (interruptor que guarda por AJAX y muestra toast) junto a “Periodos de vacaciones” (dos `<input type="date">`, nota, listado de periodos actuales y futuros con aviso de citas dentro y borrado con modal).
  - Pestañas Online/Presencial: formulario de duración (chips 30/45/50/60), descanso, horas cada 15 min y texto vivo de huecos; si cambia algo, modal de aviso antes de guardar; tras guardar, banner persistente “Revisa y vuelve a marcar tus huecos semanales (online y presencial)” hasta guardar la rejilla de cada modalidad.
  - Rejilla semanal: celdas con `aria-pressed`, clic en día/hora marca columna/fila, copiar lunes a martes–viernes, desmarcar todo, contador, aviso de cambios sin guardar (también al salir de la página) y guardado por PUT JSON.
- **Citas** (`Panel\AppointmentController`, vistas `panel/appointments/{index,create,edit,_form}.blade.php`, `pages/appointments.css`, `pages/appointment-form.js`):
  - Listado ordenado por fecha descendente, 15 por página, filtros GET (texto por nombre/apellidos palabra a palabra, teléfono normalizado o motivo; desde/hasta; modalidad; estado; origen), badge de modalidad, selector de estado que se envía solo (`modules/auto-submit.js`), editar y eliminar con modal; empty states distintos sin citas y sin resultados.
  - Formulario: datos del paciente (nombre, apellidos, teléfono, email), modalidad, fecha, huecos libres cargados por AJAX desde `panel.availability.slots` (en edición se mantiene la hora actual), interruptor “Hora personalizada” (campo `custom_time_value`), estado, origen, precio prellenado desde el plan de la modalidad, motivo y notas internas. Admite `?fecha=`, `?hora=` y `?modalidad=` para precargar (lo usará el calendario).
- Reglas de reserva aplicadas: modo vacaciones y periodos de vacaciones bloquean huecos en el panel; los huecos deben estar marcados en la rejilla; una cita ocupa `[inicio, fin + descanso)` y bloquea huecos de **cualquier** modalidad; las canceladas no ocupan; el panel no exige antelación mínima ni horizonte (solo que sea futuro), la web pública sí (2 h y 90 días).

## Calendario (Fase 5)
- `Panel\CalendarController` (`index`, `events`), vista `panel/calendar/index.blade.php`, `pages/calendar.css`, `pages/calendar.js`; FullCalendar 6 vendorizado (`index.global.min.js` + `locales/es.global.min.js`) cargado solo en esta página.
- Cabecera propia (FullCalendar sin `headerToolbar`): título del periodo, ‹ Hoy ›, vistas Mes / Semana / Día / Lista (`aria-pressed`) y botón “Nueva cita”. Aviso si el modo vacaciones está activo. Leyenda (online, presencial, cancelada, vacaciones, horario) y ayuda de uso.
- Calendario: `locale: es`, `firstDay: 1`, 24 h, franjas de 30 min de 07:00 a 22:00 con desplazamiento inicial a la hora de entrada más temprana, línea de “ahora”, `businessHours` desde la rejilla semanal, máximo 3 citas por día en la vista Mes (resto en “+N”). En móvil (≤768 px) se abre en vista Lista.
- Eventos: citas con colores por modalidad (online azul, presencial verde), canceladas tachadas y no arrastrables, citas pasadas no arrastrables; vacaciones como fondo con su nota.
- Interacciones: clic en un día (vista Mes) → “Agenda del día”; clic en una cita → modal de detalle (paciente, fecha, hora, teléfono, email, precio, origen, motivo, badges) con Cambiar estado (AJAX), Eliminar (confirmación propia + AJAX), Editar y Ver paciente; clic en un hueco vacío (Semana/Día) → `citas/crear?fecha=&hora=`; arrastrar y soltar → `PATCH /citas/{id}/mover`; si falla, se revierte y se muestra el error.
- “Agenda del día”: citas no canceladas del día seleccionado ordenadas, pasadas atenuadas, separador “Descanso · X” cuando hay 30 min o más libres entre citas (descontando el descanso configurado), enlace “Añadir cita este día” y empty state “No hay más citas programadas para hoy” / “No hay citas programadas este día”.
- “Ver paciente” lleva de momento al listado de pacientes (sección “Próximamente” hasta la Fase 7).

## Frontend del panel (base reutilizable)
- CSS (`public/panel/css/`): `fonts.css`, `variables.css` (tokens, 8 acentos `[data-accent]`, modo `[data-mode="dark"]`), `reset.css` (html 10px), `base.css`, `layout.css` (sidebar, topbar, contenido, off-canvas), `components.css` (marca, horario —`duration-field`, `break-row`, `slot-summary`, movidos desde `installer.css`—, botones, tarjetas, formularios, toggle, píldoras, avisos, chips, badges por tono, subida de imagen, toasts, avatar, cabecera de página, tarjetas de estadística, empty state, desplegables, tooltip, modales `<dialog>`, diálogo de confirmación, pestañas, barra de filtros, tablas, skeleton/spinner, paginación), `pages/installer.css`, `pages/login.css`, `pages/dashboard.css`, `pages/availability.css`, `pages/appointments.css`, `pages/calendar.css`. Se eliminó `pages/welcome.css` (página provisional sustituida).
- Imágenes del panel: `public/panel/img/acceso.jpg`.
- JS (`public/panel/js/`, módulos ES, sin `innerHTML`):
  - `core/`: `dom.js` (`el`, `icon`, `clear`), `toast.js` (`toast()`, `initToasts()`), `http.js` (`request` / `http.get|post|put|patch|delete` con CSRF y `HttpError` con mensajes en español para 401/403/404/419/422/429/500), `modal.js` (`openModal`, `closeModal`, `[data-modal-open]`, `[data-modal-close]`, clic en el fondo), `confirm.js` (`confirmAction()` con promesa y formularios `[data-confirm]`), `loader.js` (skeleton de tablas, `setBusy`, spinner), `debounce.js`.
  - `modules/`: `sidebar.js` (off-canvas y desplegables), `dropdowns.js` (`[data-dropdown]`, Esc, clic fuera), `tabs.js` (ARIA, flechas, Inicio/Fin, evento `tabs:change`), `appearance.js` (aviso provisional), `auto-submit.js` (`[data-auto-submit]`), `schedule-summary.js` (texto vivo de huecos; compartido por instalador y disponibilidad), `week-grid.js`, `wysiwyg.js`, `image-upload.js`, `chips-input.js`, `repeatable.js`, `password-toggle.js`, `loading-forms.js`.
  - `pages/`: `panel.js` (inicializa todo lo común del panel), `installer.js`, `login.js`, `availability.js`, `appointment-form.js`, `calendar.js`.
- Componentes Blade (`resources/views/components/panel/`): `page-header` (slot `actions`), `card` (título, icono, slots `actions` y `footer`, `flush`), `stat-card`, `empty-state`, `badge` (tipos `modality`, `appointment`, `patient`, `post` o tono libre), `button` (enlace o botón), `avatar`, `modal` (`<dialog>`), `confirm-delete` (formulario DELETE con `data-confirm`), `tabs` + `tab-panel`, `filters-bar`, `table` (slot `head`), `toggle-switch`, `flash`, `form/input`, `form/select`, `form/textarea`, `form/time-select`, `form/wysiwyg`, `form/image-upload`.
- Paginación propia: `resources/views/vendor/pagination/panel.blade.php` (“Mostrando X–Y de Z”, Anterior/Siguiente; usar `->links('vendor.pagination.panel')`).
- CSS público provisional: `public/assets/shared/css/coming-soon.css`.

## Tests
- `tests/TestCase.php`: marca la app como instalada en cada test (`storage/framework/testing/installed.lock`).
- `tests/Unit/PhoneTest.php` (normalización y validación de teléfonos).
- `tests/Feature/Auth/LoginTest.php`: página de acceso, login correcto (con email en mayúsculas y teléfono con espacios), teléfono/contraseña/email incorrectos, campos obligatorios, throttle, cookie de recordarme (y su ausencia), logout, logout solo por POST, redirecciones guest/auth.
- `tests/Feature/PanelRoutesProtectionTest.php`: recorre el router y exige `auth` en toda ruta `panel-psicologa*`.
- `tests/Feature/Panel/PanelNavigationTest.php`: todas las entradas del menú (más Nueva cita, Ayuda y Buscar) cargan con el layout; saludo del inicio; grupo activo desplegado; atajo `/perfil`; invitado redirigido.
- `tests/Concerns/ConfiguresAvailability.php`: congela la fecha (lunes 05/10/2026 07:00) y configura online 50+10 y presencial 50+0, de 09:00 a 14:00, de lunes a viernes.
- `tests/Unit/SlotAlgorithmTest.php`: 50+10 → 09:00…13:00; 50+0 → 09:00, 09:50, 10:40, 11:30, 12:20, 13:10; bordes.
- `tests/Feature/Availability/AvailabilityServiceTest.php`: huecos por modalidad, días sin huecos, modo vacaciones, periodos de vacaciones (público y panel), solapes entre modalidades, canceladas, ignorar la propia cita, antelación mínima, pasado, horizonte, `overlaps()`, poda y `needs_review`, guardado de rejilla, rangos.
- `tests/Feature/Availability/AvailabilityPageTest.php`: página, cambio de horario con poda y banner, validación de horas imposibles, rejilla por JSON, modo vacaciones por AJAX, alta/borrado de periodos, solapes y fechas invertidas, aviso de citas dentro del periodo.
- `tests/Feature/Appointments/AppointmentManagementTest.php`: páginas, alta con creación de paciente y copia del horario, teléfono con espacios vinculado al mismo paciente, solape rechazado con mensaje, hueco ocupado, vacaciones (hueco rechazado, hora personalizada permitida), hora fuera de rejilla, campos de hora obligatorios, edición, reactivación con solape, cambio de estado, borrado, filtros, endpoint de huecos.
- `tests/Feature/Panel/DashboardTest.php`: empty states y estadísticas reales.
- `tests/Feature/Calendar/CalendarTest.php`: página, feed de eventos (citas, canceladas, vacaciones de fondo, rango, validación), mover a hueco libre manteniendo duración, mover a hueco ocupado (422 y sin cambios), mover al pasado o una cancelada, estado y borrado por JSON, `businessHours`.
- Ninguno ejecutado (por indicación del usuario).

## Decisiones técnicas
- La Fase 0 no estaba hecha y es dependencia directa de la Fase 1: se ejecutó completa antes.
- Extensiones PHP activadas directamente en `php.ini` (con copia de seguridad) en lugar de pedírselo al usuario, para no bloquear.
- `php artisan serve` reinicia el servidor al cambiar `.env`: el paso de BD migra con la conexión configurada en caliente, renderiza la pantalla de confirmación y escribe `.env` justo al final (solo si cambia). El usuario continúa con un botón, así la siguiente petición llega al servidor ya reiniciado.
- Guarda de pasos en middleware (no en el controlador) para que se ejecute antes de los Form Requests, que consultan la BD.
- Datos de los pasos 3–7 persistidos directamente en BD (ya existe tras el paso 2); en sesión solo se guarda el progreso.
- El contenido de ejemplo se carga al finalizar (no al elegir tema), para que volver atrás no lo mezcle con los datos del asistente.
- Paso de foto opcional; las especialidades, servicios y precios también, salvo nombre público, teléfono y email de citas.
- Se crearon como dependencia directa piezas que el plan asigna a fases posteriores, en versión mínima y ampliable: CSS base y componentes Blade/JS del panel (Fase 3), `ThemeManager` de descubrimiento (Fase 12), `AvailabilityService::grid()` (Fase 4) y una ruta provisional `/panel-psicologa`.

- Fase 2: autenticación con el guard de sesión nativo de Laravel (sin paquetes extra). El login no usa `Auth::attempt` porque hay que validar tres datos; la verificación es manual pero con el mismo hashing y la misma cookie *remember me*.
- Fase 2: errores de credenciales y de bloqueo en una sola clave (`login`) mostrada como aviso general, sin indicar qué dato falla.
- Fase 2: los estilos `.brand` se movieron de `pages/installer.css` a `components.css` para reutilizarlos (login y, en la Fase 3, sidebar).
- Fase 2: se añadió un botón “Cerrar sesión” provisional en el inicio del panel; desde la Fase 3 el cierre de sesión está en el pie del sidebar y en el menú del avatar.
- Fase 3: menú y secciones pendientes definidos en `config/panel.php` para no duplicar datos entre sidebar, rutas y páginas “Próximamente”; las rutas provisionales ya usan los nombres y URLs definitivos del plan.
- Fase 3: modales con `<dialog>` nativo (foco, Esc y fondo resueltos por el navegador) y diálogo de confirmación propio creado con `el()`, sin `confirm()`.
- Fase 3: el botón de apariencia muestra un aviso hasta la Fase 14; la campana muestra un estado vacío hasta la Fase 15.
- Fase 3: la paginación propia no es la vista por defecto global (para no afectar a la paginación pública de los temas); se pasa explícitamente.
- Fase 3: `pages/welcome.css` eliminado; la página de inicio usa ya el layout definitivo.
- Fase 4: al cambiar la duración, el descanso o las horas de una modalidad se marcan para revisión **las dos** modalidades (CLAUDE.md pide avisar de revisar online y presencial); el banner desaparece por modalidad al guardar su rejilla.
- Fase 4: el formulario de horario se envía como formulario normal (redirección + toast) y la rejilla por JSON; el modo vacaciones por JSON.
- Fase 4: el panel puede crear citas fuera de la disponibilidad con “Hora personalizada” (incluso en vacaciones), pero nunca solapadas.
- Fase 4: las reglas de horario del instalador y de la disponibilidad comparten el trait `App\Http\Requests\Concerns\ValidatesSchedule`; `Installer\ScheduleRequest` se refactorizó para usarlo (mismo comportamiento).
- Fase 4: `pages/installer.js` usa ahora `modules/schedule-summary.js` (mismo comportamiento).
- Fase 4: solapes calculados en PHP sobre las citas cercanas (en lugar de aritmética de fechas en SQL) para que funcione igual en MySQL y en SQLite (tests).
- Fase 5: mover una cita desde el calendario se trata como “hora personalizada”: no exige que el destino esté en la rejilla semanal (permite reorganizar libremente), pero sí que sea futuro, que la cita no esté cancelada y que no se solape.
- Fase 5: las fechas del calendario se manejan como hora “de pared” (sin zona horaria) en ambos sentidos para que lo que ve la psicóloga coincida con lo guardado; el feed solo usa la fecha de `start`/`end`.
- Fase 5: cambio de estado y borrado reutilizan las rutas existentes respondiendo JSON cuando la petición lo pide (sin rutas nuevas).
- Fase 5: se añadió la vista Lista además de Mes/Semana/Día (el plan pide vista lista/día en móvil).
- Prompt 8: moneda cambiada de euros a **pesos colombianos (COP)**. Todo importe visible usa `money()`; los campos de precio muestran el sufijo “COP”, van en pasos de 1 y aceptan hasta 999.999 (validación `max` desde la config). Las columnas `price` siguen siendo `decimal(8,2)` (admiten hasta 999.999,99), así que no hizo falta migración. Iconos `fa-euro-sign` sustituidos por `fa-dollar-sign`. Precios de ejemplo (seeder y factories) en COP: 150.000 online y 180.000 presencial.
- Requisito registrado (prompt 4): la web pública y los 5 temas seguirán la estructura de `tema-visual-base` (`index.html` para la portada, `interior.html` para páginas interiores). Equivalencia bloque a bloque en `plan-implementación.md`, sección 3.1. Sin cambios de código: se aplicará en las Fases 12 y 16.

## Impacto en el sistema
- Sin `installed.lock`, cualquier URL web redirige a `/instalacion`. Tras instalar, el instalador devuelve 404.
- Para reinstalar desde cero: borrar `psicocms/storage/app/installed.lock` y la base de datos.
- Todas las rutas `/panel-psicologa*` (inicio, secciones provisionales, perfil y cierre de sesión) requieren sesión; sin ella redirigen a `/acceso-psicologa`.
- La navegación del panel ya no da 404 en ninguna entrada del menú.
- Inicio, Citas (listado, alta, edición), Calendario y Disponibilidad son ya funcionales; Pacientes, Historias, Blog, Mi web y Configuración siguen como “Próximamente”.
- La disponibilidad configurada (rejilla, vacaciones, modo vacaciones) ya condiciona los huecos del formulario de citas del panel.
- `/acceso-psicologa` solo es accesible sin sesión; con sesión redirige al panel.

## Registro de cambios
- Proyecto Laravel creado en `psicocms/`; ficheros de seguimiento creados.
- Fase 0: configuración, idioma, librerías, esquema completo de BD, modelos, factories, seeders, servicios base, middleware y tests de `Phone`.
- Fase 1: asistente de instalación completo de 8 pasos + manifiestos de los 5 temas.
- Prompt 4: estructura de `tema-visual-base` registrada en el plan (sección 3.1).
- Fase 2: login seguro en `/acceso-psicologa`, recordarme, throttle, logout y tests.
- Fase 3: layout del panel (sidebar, topbar, móvil), componentes Blade, JS núcleo, páginas “Próximamente” y test de navegación.
- Fase 4: inicio con estadísticas reales, disponibilidad completa (horarios, rejilla, vacaciones, modo vacaciones), gestión de citas con solapes, servicios de disponibilidad/citas/pacientes y tests.
- Prompt 8: moneda en pesos colombianos (config `currency`, helper `money()`, vistas del instalador, inicio y citas, validación, seeders, factories y tests; nuevo `tests/Feature/MoneyFormatTest.php`).
- Fase 5: calendario con FullCalendar (vistas, agenda del día, detalle, mover arrastrando, estado y borrado por AJAX, vacaciones y horario) y tests.
