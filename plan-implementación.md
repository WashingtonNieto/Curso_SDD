# Plan de implementación — PsicoCMS

> Destinatario: agente de IA (Claude Code con Claude Sonnet 5) que implementará el proyecto fase a fase.
> Fuente de verdad funcional: `CLAUDE.md`. Este plan concreta el *cómo*. Si algo choca, manda `CLAUDE.md`; si hay duda, pregunta al usuario.

---

## 0. Contexto

- El repositorio (`D:\xampp\htdocs\Curso_sdd`) **no contiene todavía código de la aplicación**: solo `CLAUDE.md`, `tema-visual-base/` (maquetación HTML/CSS/JS del tema público: `index.html`, `interior.html`, `assets/{css,js,img,fonts}`) y `dashboard-design/` (5 capturas del prototipo del panel: inicio, calendario, pacientes, blog, disponibilidad).
- Objetivo: construir un CMS monolítico en Laravel para una psicóloga independiente: web pública con 5 temas, reservas de citas, y panel privado (`/panel-psicologa`) con citas, calendario, pacientes, historias clínicas, blog, configuración de la web, etc.
- Decisiones confirmadas por el usuario:
  - Calendario del panel: **FullCalendar v6** (vanilla JS, vendorizado en local).
  - Proyecto Laravel en la subcarpeta **`psicocms/`**, servido con **`php artisan serve`** (http://127.0.0.1:8000) y MySQL/MariaDB de XAMPP.

### Entorno detectado
| Herramienta | Versión | Nota |
|---|---|---|
| PHP | 8.2.12 (XAMPP) | ⇒ **Laravel 12** (Laravel 13 exige PHP 8.3) |
| Composer | 2.10.2 | |
| MariaDB | 10.4.32 | |
| Node | 20.20.2 | **No se usará** (sin Vite, sin npm: CSS/JS nativos servidos desde `public/`) |
| Extensiones PHP | PDO, pdo_mysql, pdo_sqlite, mbstring, openssl, fileinfo, curl | **Faltan `gd`, `zip`, `intl`** → hay que activarlas en `D:\xampp\php\php.ini` (prerrequisito, Fase 0) |

---

## 1. Decisiones técnicas

- **Backend**: Laravel 12, PHP 8.2, MySQL (MariaDB). `APP_LOCALE=es`, `APP_TIMEZONE=Europe/Madrid`, Carbon en español.
- **Drivers** (para que el instalador funcione sin BD previa): `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`.
- **Frontend**: HTML5 + CSS3 nativo + JS nativo con módulos ES (`<script type="module">`). Sin Vite. Se eliminan las directivas `@vite` del esqueleto.
- **Librerías vendorizadas** (descargadas del CDN a `public/vendor/`, nunca enlazadas al CDN):
  - Font Awesome 6.1.2 → copiar desde `tema-visual-base/assets/fonts/fontawesome-free-6.1.2-web/` (solo `css/` y `webfonts/`) a `public/vendor/fontawesome/`. Lo usan panel y todos los temas.
  - Jodit 4 (build “fat”, incluye idioma `es`) → `public/vendor/jodit/` (`jodit.fat.min.js`, `jodit.min.css`). Doc: https://xdsoft.net/jodit/docs/getting-started.html
  - FullCalendar 6 (`index.global.min.js` + `locales/es.global.min.js`) → `public/vendor/fullcalendar/`.
- **Paquetes Composer**:
  - `barryvdh/laravel-dompdf` → PDF de protección de datos (requiere `gd`).
  - `mews/purifier` → sanear todo HTML que venga de Jodit (XSS).
- **Email**: Mailer nativo de Laravel (Symfony Mailer) con SMTP de Gmail + contraseña de aplicación, configurado en tiempo de ejecución desde la BD. Envío con `dispatch(...)->afterResponse()` (sin worker de colas).
- **Autenticación**: guard `web` de sesión de Laravel, contraseña con `Hash` (bcrypt, coste por defecto de Laravel 12), cookie “remember me” nativa, `RateLimiter`.
- **Mapa**: iframe de Google Maps sin API key (`https://www.google.com/maps?q={dirección}&output=embed`).
- **Google Calendar del paciente**: enlace `https://calendar.google.com/calendar/render?action=TEMPLATE&text=…&dates=…&details=…&location=…` (sin API) + descarga `.ics`.
- **Tests**: PHPUnit (incluido en Laravel) con SQLite en memoria (`pdo_sqlite` ya está disponible). Las migraciones deben ser portables (nada exclusivo de MySQL).

---

## 2. Reglas transversales para el agente (aplican en TODAS las fases)

1. **Seguimiento**: antes de empezar, crear en la raíz del repo `plan-implementación.md` (copia de este plan), `tareas.md` (checklist por fase con `[ ]`/`[x]`) y `prompts.md` (prompt nº1 = la petición de este plan). Actualizar `tareas.md` al completar cada tarea. Cada nuevo prompt del usuario se añade al final de `prompts.md`.
2. **Una fase cada vez**. Al terminar una fase: ejecutar `php artisan test`, revisar el checklist de aceptación, actualizar `tareas.md` y **parar** para que el usuario pruebe. No empezar la siguiente sin su visto bueno. No hacer commits salvo que el usuario lo pida.
3. **No romper lo anterior**: ejecutar toda la suite de tests al final de cada fase.
4. **Rutas**: todo el panel bajo `/panel-psicologa` con middleware `auth`; login en `/acceso-psicologa`. Toda acción del panel (incluido AJAX) protegida y con CSRF.
5. **Validación** con Form Requests y mensajes en español. **Solapamientos** validados en servidor (citas, periodos de vacaciones).
6. **Feedback**: nunca `alert/confirm/prompt`. Toasts para confirmaciones; modal propio para confirmar borrados. Mismo estilo que el panel/tema.
7. **DOM**: prohibido `innerHTML`. Crear nodos con `document.createElement`/helper `el()` y `appendChild`/`append`/`replaceChildren`. Siempre `let`/`const`. `event.preventDefault()` en todos los submit/click que lo requieran.
8. **Empty states** agradables (icono FA + texto + CTA) en todo listado vacío.
9. **CSS**: unidades `rem` con `html { font-size: 10px }`. CSS separado por contexto: `public/panel/css/…` para el panel y `themes/{slug}/assets/css/…` para cada tema. Flexbox/Grid. Responsive en todo.
10. **Textos visibles en español**. Código mínimamente comentado y autoexplicativo. No explicar el código en el chat.
11. **Reutilización**: usar siempre los componentes Blade y módulos JS núcleo (sección 5) en lugar de duplicar.
12. **Seguridad**: HTML de Jodit saneado con Purifier antes de guardar; uploads validados por mime/tamaño y con nombre aleatorio; documentos clínicos en disco **privado** servidos por controlador autenticado; secretos (contraseña de Gmail) cifrados con `Crypt`; cabeceras de seguridad (middleware `SecurityHeaders`: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`).
13. **Teléfonos** siempre normalizados con `App\Support\Phone::normalize()` antes de guardar o buscar.
14. Ambigüedad ⇒ la opción más simple que no rompa nada; si afecta al alcance, preguntar.

---

## 3. Arquitectura y estructura de carpetas

```
Curso_sdd/
├── CLAUDE.md · plan-implementación.md · tareas.md · prompts.md
├── tema-visual-base/        (referencia, no se modifica)
├── dashboard-design/        (referencia, no se modifica)
└── psicocms/                (proyecto Laravel 12)
    ├── app/
    │   ├── Http/Controllers/{Installer,Auth,Panel,Site}/
    │   ├── Http/Middleware/ (EnsureInstalled, RedirectIfInstalled, EnsureModuleEnabled, SecurityHeaders)
    │   ├── Http/Requests/{Panel,Site,Installer}/
    │   ├── Models/
    │   ├── Services/        (ver sección 5)
    │   ├── Support/         (Phone, EnvWriter, helpers.php)
    │   ├── Mail/            (NewAppointmentMail, TestMail)
    │   └── View/Components/Panel/
    ├── config/psicocms.php  (paletas, slots de imágenes, módulos, horizonte de reservas…)
    ├── config/phrases.php   (catálogo de frases públicas por defecto)
    ├── database/{migrations,seeders,factories}/
    ├── lang/es/             (validation.php, auth.php, pagination.php, passwords.php)
    ├── public/
    │   ├── panel/{css,js,img,fonts}/   (CSS y JS del panel)
    │   ├── assets/shared/js/           (booking.js y JS público compartido)
    │   ├── vendor/{fontawesome,jodit,fullcalendar}/
    │   └── storage → storage/app/public (php artisan storage:link)
    ├── resources/views/
    │   ├── installer/  auth/  panel/  components/panel/  emails/  pdf/
    ├── routes/ web.php (incluye installer.php, auth.php, panel.php, site.php)
    ├── storage/app/private/clinical/   (adjuntos de historias, NO públicos)
    ├── themes/                         (plug & play, ver Fase 12)
    │   └── {slug}/ theme.json · views/ · assets/{css,js,img,fonts}/
    └── tests/{Feature,Unit}/
```

**Temas plug & play**: cada tema es una carpeta autocontenida en `psicocms/themes/{slug}`. `ThemeManager` la descubre leyendo `theme.json`. Las vistas se registran con `View::addNamespace('theme', themes/{activo}/views)`. Los assets se sirven por la ruta `GET /themes/{slug}/{path}` (`ThemeAssetController`: valida que el tema existe, `realpath` dentro de `assets/` para evitar path traversal, mime correcto, `Cache-Control: public, max-age=31536000` + `?v={version}`). Añadir un tema = copiar una carpeta.

### 3.1 Estructura obligatoria de la web pública (basada en `tema-visual-base`)

> Requisito del usuario (prompt 4): la web pública debe respetar la estructura de `tema-visual-base`. Aplica a los 5 temas (Fase 12) y a las rutas/modos de la Fase 16. Los temas 2–5 pueden cambiar paleta, tipografía y composición, pero **mantienen este orden de bloques y su marcado BEM** (`layout__*`, `banner__*`, `services__*`…), adaptando solo lo necesario.

**Portada / landing (`index.html`)** — orden de bloques y su equivalencia:

| Bloque del tema base | Parcial Blade | Datos de PsicoCMS | Decisión |
|---|---|---|---|
| `layout__background` + `layout__container-banner` (`layout__nav` escritorio y `layout__nav-mobile`: logo, nombre, subtítulo, enlaces, caja de contacto, redes en móvil) | `partials/header`, `partials/nav` | `site.public_name`, frase de subtítulo, `NavigationBuilder`, teléfono/email/WhatsApp, redes | Se mantiene. El nombre deja de ser `h1` (un único `h1` por página) |
| `layout__banner#home` (forma `sandwich`, título, botón de cita, `psicologa.png`, formas `shape1/2`) | `sections/hero` | `site.slogan`, `site.photo` o `theme_image('hero')`, CTA “Pide cita” / Llamar / WhatsApp | Se mantiene |
| `layout__therapies` (3 tarjetas con `terapia1-3.jpg`) | `sections/specialties` | `specialties` activas | Se mantiene (módulo Especialidades) |
| `layout__characteristics#about` | `sections/about` | `site.about`, foto, características | Se mantiene |
| `layout__services#services` (`servicio1-4.jpg`, `bg-services.png`) | `sections/services` | `services` activos | Se mantiene (módulo Servicios) |
| `layout__couple-issues` | `sections/cta-intermedio` | Frases públicas | Se mantiene si hay frase configurada |
| `layout__how-start` (`why.jpg`) | `sections/how-start` | Frases públicas + `theme_image('como-empezar')` | Se mantiene |
| `layout__prices` (online / presencial / grupo) | `sections/prices` | `plans` activos | Se mantiene (módulo Planes) |
| `layout__stats` (`bg-stats.png`) | `sections/stats` | Años de experiencia, nº de pacientes, etc. (frases) | Se mantiene |
| `layout__cases#cases` (`casos1-3.jpg`) | — | Sin datos en el sistema | Se descarta |
| `layout__promos` (“Trabajo en:”) | — | Sin datos en el sistema | Se descarta |
| `layout__experience` (`exp1.jpg`) | `sections/experience` | Formación/experiencia (Fase 11) | Se mantiene si hay datos |
| `layout__blog#blog` (`blog1-3.jpg`) | `sections/blog-latest` | 3 últimos `blog_posts` publicados | Se mantiene (módulo Blog) |
| *(nuevo, con el estilo del tema)* | `sections/faq` | `faqs` activas | Añadido (módulo FAQ) |
| *(nuevo, con el estilo del tema)* | `sections/booking` + `sections/location` | Widget de reservas + mapa | Añadido (módulos Reservas y Mapa) |
| `layout__appointment` (`bg-contactnow.png`, `shape-contactnow.png`) | `sections/cta` | CTA a Pide cita | Se mantiene |
| `layout__footer`: top (icono, horario, redes) · middle (logo + descripción, Explorar, Contacto, Newsletter) · bottom (copyright) | `partials/footer` | Horario, redes, navegación, contacto | Se mantiene; **Newsletter se descarta** (no hay funcionalidad) |
| `layout__container-go-top` | `partials/go-top` | — | Se mantiene |

**Páginas interiores (`interior.html`)** — base de todas las páginas del modo multipágina (Sobre mí, Servicios, Especialidades, Preguntas frecuentes, Pide cita, detalle de blog, textos legales): misma cabecera/nav (`layout__container-banner`), `layout__container-main` con `layout__aside` (lateral: servicios, contacto y CTA “Haz tu cita ahora”) + `layout__main` (contenido), y el mismo pie. En “Pide cita” el `layout__main` contiene la reserva y el `layout__aside` el “¿Dónde estamos?”.

**Assets**: se copian a cada tema `assets/css/{reset,fonts,styles,responsive}.css`, `assets/js/{banner,main,navFixed,navMobile,scroll-top,video}.js` (revisados para cumplir las reglas: sin `innerHTML`, sin `var`) y `assets/img/*` como imágenes por defecto de los slots.

---

## 4. Modelo de datos (se crea completo en la Fase 0)

| Tabla | Campos principales |
|---|---|
| `users` | id, first_name, last_name, email (único), phone (único, normalizado), password, avatar_path, panel_mode (`light`/`dark`), panel_color (slug de paleta, def. `azul`), remember_token, timestamps |
| `settings` | id, key (único, con prefijos `site.`, `modules.`, `theme.`, `mail.`, `social.`, `seo.`, `privacy.`, `booking.`), value (longText, JSON cuando aplique), timestamps |
| `availability_settings` | id, modality (`online`/`presencial`, único), session_duration (min), break_enabled, break_minutes, day_start (time), day_end (time), needs_review (bool), timestamps |
| `availability_slots` | id, modality, weekday (1=lun…7=dom, ISO), start_time (time) · único(modality, weekday, start_time) |
| `vacation_periods` | id, start_date, end_date, note, timestamps |
| `patients` | id, phone (único, normalizado), first_name, last_name, email, birth_date, gender, dni, address, city, postal_code, occupation, emergency_contact_name, emergency_contact_phone, preferred_modality, status (`activo`/`pausado`/`alta`), therapy_type, reason, notes, source (`web`/`manual`), privacy_signed_at, timestamps, softDeletes |
| `appointments` | id, patient_id (FK), modality, starts_at, ends_at, break_minutes (snapshot), status (`pendiente`/`confirmada`/`completada`/`cancelada`/`no_asistio`), source (`web`/`telefono`/`email`/`whatsapp`/`presencial`/`otro`), reason, internal_notes, price (decimal nullable), public_token (uuid único), seen_at (nullable), timestamps · índices en starts_at y status |
| `clinical_entries` | id, patient_id (FK), appointment_id (FK nullable, nullOnDelete), session_date, title, content (longText), timestamps, softDeletes |
| `clinical_attachments` | id, clinical_entry_id (FK cascade), path, original_name, mime, size, timestamps |
| `services` | id, title, slug (único), icon (clase FA), excerpt, content, image_path, sort_order, is_active, timestamps |
| `specialties` | id, name, slug, description, icon, sort_order, is_active, timestamps |
| `plans` | id, name, modality (`online`/`presencial`/`ambas`), price, duration_label, description, features (una por línea), is_featured, sort_order, is_active, timestamps |
| `faqs` | id, question, answer, sort_order, is_active, timestamps |
| `blog_categories` | id, name, slug (único), description, timestamps |
| `blog_posts` | id, blog_category_id (FK nullable, nullOnDelete), title, slug (único), excerpt, content, image_path, status (`borrador`/`publicado`), published_at, meta_description, timestamps |
| `site_images` | id, key (único, de `config('psicocms.image_slots')`), path, timestamps |
| `phrases` | id, key (único, de `config/phrases.php`), value, timestamps |

Las citas canceladas no ocupan hueco. Redes sociales, módulos, datos públicos, SEO, correo y plantilla RGPD viven en `settings`.

**Seeders**: `BaseDataSeeder` (categorías de blog por defecto: *Ansiedad y estrés, Salud mental, Relaciones y pareja, Autoestima y crecimiento personal, Bienestar emocional, Terapia y psicología*; availability_settings para ambas modalidades; módulos activados; plantilla RGPD por defecto; tema `calma` en modo landing). `DemoDataSeeder` (pacientes, citas pasadas/futuras, historias, 3 artículos con `blog1-3.jpg`, servicios, especialidades, planes y FAQs de ejemplo). El instalador ofrece “Cargar contenido de ejemplo”.

---

## 5. Núcleo reutilizable

### Servicios (`app/Services`, `app/Support`)
| Clase | Responsabilidad |
|---|---|
| `Support\Phone` | `normalize()`: trim, quita espacios, guiones, puntos y paréntesis; conserva `+` inicial. Validación `^\+?\d{6,15}$`. Mutator en `User` y `Patient`. |
| `Support\EnvWriter` | Escribe/actualiza claves del `.env` (instalador). |
| `Support/helpers.php` (autoload `files`) | `setting()`, `phrase()`, `theme_asset()`, `theme_image()`, `site_image()`. |
| `SettingsService` | get/set/many con caché `rememberForever` e invalidación al guardar. Cifra claves sensibles (`mail.app_password`). |
| `AvailabilityService` | **Fuente única de disponibilidad** (público, formulario de citas del panel, calendario). `grid($modality)`, `slotsForDate($modality, $date, $ignoreAppointmentId = null)`, `availableDates($modality, $month)`, `isBookable($modality, $start, $ignoreId)`, `overlaps($start, $end, $ignoreId)`. |
| `AppointmentService` | create/update/move dentro de `DB::transaction` con `lockForUpdate` y revalidación de solapamiento; `findOrCreate` del paciente por teléfono; precio por defecto desde `plans`. |
| `PatientService` | `findOrCreateByPhone($data)` (si existe no sobrescribe datos clínicos; solo completa vacíos). |
| `ThemeManager` | Descubre temas, tema activo, modo (`landing`/`multipage`), registro del namespace de vistas, manifiesto, previsualización. |
| `NavigationBuilder` | Menú público según modo y módulos activos (anclas `#sobre-mi` en landing, rutas en multipágina). |
| `HtmlSanitizer` | Envoltorio de `mews/purifier` con perfil para Jodit. |
| `ImageUploader` | Guarda en disco `public` (`uploads/{carpeta}`), nombre aleatorio, borra el anterior. |
| `MailConfigurator` | Aplica en runtime la config SMTP de Gmail desde settings. |
| `PrivacyDocumentService` | Sustituye marcadores de la plantilla y genera el PDF con dompdf. |
| `DashboardStatsService` | Métricas del inicio. |
| `GlobalSearchService` | Búsqueda transversal del panel. |

**Algoritmo de huecos (crítico, testear)**:
`paso = duración + (descanso activo ? minutos_descanso : 0)`; desde `day_start`, mientras `t + duración <= day_end`: añadir `t`; `t += paso`.
- 50 + 10 min, 09:00–14:00 → 09:00, 10:00, 11:00, 12:00, 13:00.
- 50 + 0 min, 09:00–14:00 → 09:00, 09:50, 10:40, 11:30, 12:20, 13:10.

Un hueco del día *D* es reservable si: el modo vacaciones está desactivado; *D* no cae en ningún periodo de vacaciones; el hueco está marcado en la rejilla semanal de esa modalidad; está en el futuro (antelación mínima `config('psicocms.booking.min_notice_hours')`, def. 2) y dentro del horizonte (`max_days_ahead`, def. 90); y el intervalo `[inicio, inicio + duración + descanso)` no se solapa con ninguna cita no cancelada **de cualquier modalidad** (ocupación de una cita = `[starts_at, ends_at + break_minutes)`).

### Componentes Blade del panel (`resources/views/components/panel/`)
`page-header` (título, subtítulo, acciones), `card`, `stat-card`, `empty-state`, `badge` (estado/modalidad), `button`, `modal`, `confirm-delete` (form + modal), `toggle-switch`, `tabs`, `filters-bar`, `table`, `flash` (toasts desde sesión), `form.input`, `form.select`, `form.textarea`, `form.wysiwyg`, `form.image-upload` (con previsualización), `form.time-select`. Paginación propia: `resources/views/vendor/pagination/panel.blade.php` (“Mostrando 1–10 de 42”, Anterior/Siguiente).

### JS núcleo del panel (`public/panel/js/core/`, módulos ES)
`dom.js` (`el(tag, attrs, ...children)`, `clear(node)`), `http.js` (fetch JSON con `X-CSRF-TOKEN` de `<meta>`, manejo 422/419/500), `toast.js`, `modal.js`, `confirm.js` (formularios `[data-confirm]` → modal → submit), `loader.js` (skeleton/spinner en tablas), `debounce.js`. Módulos de funcionalidad en `public/panel/js/modules/` (`sidebar.js`, `wysiwyg.js`, `availability.js`, `appointment-form.js`, `patient-autocomplete.js`, `patients-table.js`, `calendar.js`, `dropzone.js`, `sortable-list.js`, `appearance.js`, `topbar-search.js`, `notifications.js`).

### CSS del panel (`public/panel/css/`)
`variables.css` (tokens con `--color-primary` y derivados vía `color-mix()`; bloques `[data-mode="dark"]` y `[data-accent="…"]` desde el principio), `reset.css`, `base.css`, `layout.css` (sidebar, topbar, contenido), `components.css`, `pages/*.css`. Fuente: Manrope (autoalojada en `public/panel/fonts/`, como en el prototipo) con fallback a Lexend. Colores del prototipo: primario azul sereno ≈ `#3D5F8A`, fondo `#F7F8FC`, tarjetas blancas con radio grande y sombra suave, chips verde (presencial) y azul (online).

---

## 6. Mapa de rutas

**Instalador** (solo si no instalado): `/instalacion`, `/instalacion/{paso}`.
**Auth**: `GET|POST /acceso-psicologa` (guest), `POST /panel-psicologa/cerrar-sesion`.
**Panel** (`prefix panel-psicologa`, `name panel.`, `middleware auth`):

| Ruta | Uso |
|---|---|
| `/` | Inicio |
| `/citas` (resource) · `/citas/{cita}/estado` PATCH · `/citas/{cita}/mover` PATCH | Gestión de citas |
| `/calendario` · `/calendario/eventos` (JSON) | Calendario |
| `/disponibilidad` · `/disponibilidad/{modalidad}/configuracion` PUT · `/disponibilidad/{modalidad}/huecos-semanales` PUT · `/disponibilidad/modo-vacaciones` PATCH · `/disponibilidad/vacaciones` POST · `/disponibilidad/vacaciones/{periodo}` DELETE · `/disponibilidad/huecos` (JSON por fecha) | Disponibilidad |
| `/pacientes` (resource) · `/pacientes/listado` (JSON) · `/pacientes/buscar` (JSON autocompletado) · `/pacientes/{paciente}/proteccion-datos.pdf` | Pacientes |
| `/historias` · `/pacientes/{paciente}/historia` · `/historias/{entrada}` (CRUD) · `/historias/adjuntos/{adjunto}` (ver/descargar/borrar) | Historias clínicas |
| `/blog/articulos` (resource) · `/blog/categorias` (resource) · `/editor/imagenes` POST | Blog / imágenes de Jodit |
| `/mi-web/datos` · `/mi-web/sobre-mi` · `/mi-web/servicios` · `/mi-web/especialidades` · `/mi-web/planes` · `/mi-web/preguntas-frecuentes` (+ `/orden`) · `/mi-web/imagenes` · `/mi-web/frases` · `/mi-web/redes-sociales` · `/mi-web/temas` (+ `/{tema}/previsualizar`, `/activar`) · `/mi-web/seo` · `/mi-web/textos-legales` | Gestión de la web pública |
| `/configuracion/general` (= `/perfil`) · `/configuracion/apariencia` PATCH · `/configuracion/modulos` · `/configuracion/email` (+ `/prueba`) · `/configuracion/proteccion-datos` (+ `/plantilla.pdf`) | Configuración |
| `/buscar` (+ `/buscar/sugerencias` JSON) · `/ayuda` · `/notificaciones` (JSON) · `/notificaciones/vistas` PATCH | Utilidades |

**Pública**: `/`, `/sobre-mi`, `/servicios`, `/especialidades`, `/blog`, `/blog/categoria/{slug}`, `/blog/{slug}`, `/preguntas-frecuentes`, `/pide-cita`, `/contacto` (→ 301 a `/pide-cita#donde-estamos`), `/politica-de-privacidad`, `/reservas/dias` (JSON), `/reservas/huecos` (JSON), `POST /reservas`, `/reservas/{token}.ics`, `/sitemap.xml`, `/robots.txt`, `/themes/{slug}/{path}`.

---

## 7. Fases

Cada fase indica **Tareas**, **Archivos clave** y **Aceptación**. Al cerrar cada una: `php artisan test` + actualizar `tareas.md` + PAUSA para pruebas del usuario.

### FASE 0 — Preparación y base de datos ✅ COMPLETADA (pendiente de validación del usuario)
**Tareas**
1. Crear `plan-implementación.md`, `tareas.md`, `prompts.md` en la raíz.
2. Pedir al usuario que active `extension=gd`, `extension=zip`, `extension=intl` en `D:\xampp\php\php.ini` (descomentar) y verificar con `php -m`.
3. `composer create-project laravel/laravel:^12.0 psicocms`. Instalar `barryvdh/laravel-dompdf` y `mews/purifier`.
4. Configurar `.env.example`/`config`: locale `es`, timezone `Europe/Madrid`, drivers file/sync. Añadir `lang/es/*` (validation, auth, pagination, passwords) con traducciones completas.
5. Eliminar Vite del esqueleto (`vite.config.js`, `package.json` scripts, `@vite`).
6. Vendorizar Font Awesome, Jodit y FullCalendar en `public/vendor/`. Autoalojar Manrope.
7. Crear **todas** las migraciones, modelos (casts, relaciones, scopes `active()`, `published()`, `upcoming()`), factories y seeders de la sección 4.
8. Crear `config/psicocms.php` (paletas del panel, slots de imágenes, módulos, parámetros de reserva) y `config/phrases.php` (vacío de momento, se rellena en Fase 15).
9. `Support\Phone`, `SettingsService`, helpers, middleware `SecurityHeaders`, `routes/*.php` separados.
10. Tests unitarios de `Phone::normalize`.

**Aceptación**: `php artisan migrate:fresh --seed` funciona contra MySQL; `php artisan test` en verde; `php artisan serve` muestra la página por defecto.

### FASE 1 — Asistente de instalación ✅ COMPLETADA (pendiente de validación del usuario)
**Tareas**
1. Middleware global `EnsureInstalled`: si no existe `storage/app/installed.lock`, redirige todo a `/instalacion` (excepto rutas del instalador y `/themes/*`). `RedirectIfInstalled` bloquea el instalador una vez instalado (404).
2. Si falta `.env` o `APP_KEY`, crearlos desde `.env.example` y generar la clave antes de iniciar sesión (service provider temprano).
3. Asistente multipaso (layout propio con barra de progreso, estilo del panel; datos de cada paso en sesión hasta poder persistir):
   1. **Bienvenida y requisitos**: versión de PHP, extensiones (pdo_mysql, mbstring, gd, fileinfo, openssl), permisos de escritura de `storage/` y `bootstrap/cache/`.
   2. **Base de datos**: host, puerto, nombre (regex `^[A-Za-z0-9_]+$`), usuario, contraseña → probar conexión PDO al servidor, `CREATE DATABASE IF NOT EXISTS … utf8mb4_unicode_ci`, escribir `.env` con `EnvWriter`, `config()->set` + `DB::purge()`, `Artisan::call('migrate', ['--force' => true])` y `BaseDataSeeder`. Errores mostrados de forma amable.
   3. **Cuenta de acceso**: nombre, apellidos, email, teléfono, contraseña + confirmación (`Password::min(8)->letters()->numbers()`). Si el usuario ya existe (reanudación), se actualiza; nunca se crea un segundo.
   4. **Datos públicos**: nombre público, eslogan, nº colegiado, teléfono y email para citas, WhatsApp, dirección y ciudad, “Sobre mí” (Jodit), especialidades (chips añadibles), servicios principales (lista repetible título + descripción), planes y precios online/presencial.
   5. **Horarios**: por modalidad, duración de sesión, descanso (on/off + minutos), hora de entrada/salida y días laborables → genera la rejilla inicial con `AvailabilityService::grid()`.
   6. **Foto**: subida con previsualización y aviso “Preferiblemente en PNG sin fondo”.
   7. **Tema**: las 5 tarjetas de tema (mini maqueta CSS desde `theme.json`) + modo landing/multipágina + checkbox “Cargar contenido de ejemplo”.
   8. **Finalizar**: escribir `installed.lock`, `Auth::login($user)`, redirigir a `/panel-psicologa` con toast de bienvenida.
4. En esta fase se crean ya las carpetas `themes/{slug}/theme.json` de los 5 temas (solo manifiestos) para poder elegirlos; las vistas se construyen en Fase 12.

**Archivos clave**: `app/Http/Controllers/Installer/InstallerController.php`, `app/Http/Requests/Installer/*`, `resources/views/installer/*`, `app/Support/EnvWriter.php`, `public/panel/css/pages/installer.css`.
**Aceptación**: con la BD inexistente, el asistente la crea, migra, guarda todos los datos y termina logueado en el panel; volver a `/instalacion` da 404; recargar a mitad de pasos reanuda sin duplicar.

### FASE 2 — Login seguro ✅ COMPLETADA (pendiente de validación del usuario)
**Tareas**
1. `GET|POST /acceso-psicologa` (middleware `guest`). Formulario con email, teléfono y contraseña (los 3 obligatorios), checkbox deslizante “Mantener la sesión iniciada”, mostrar/ocultar contraseña.
2. Lógica: normalizar teléfono; buscar por email; verificar que el teléfono coincide **y** `Hash::check`; mensaje genérico “Los datos de acceso no son correctos”. `Auth::login($user, $remember)`, `session()->regenerate()`.
3. `RateLimiter`: 5 intentos/minuto por email+IP, mensaje con segundos restantes.
4. Logout por POST con invalidación de sesión y regeneración de token. Redirección de invitados a `/acceso-psicologa` (`redirectGuestsTo`).
5. Diseño de login coherente con el panel (tarjeta centrada, ilustración/imagen lateral del tema base).

**Aceptación**: tests de feature: login correcto, teléfono incorrecto, contraseña incorrecta, campo vacío, throttle, “recordarme” crea cookie, logout. Test que recorre `Route::getRoutes()` y comprueba que **toda** ruta `panel-psicologa*` tiene middleware `auth`.

### FASE 3 — Layout y menú del panel ✅ COMPLETADA (pendiente de validación del usuario)
**Tareas**
1. Layout `resources/views/panel/layout.blade.php` con `data-mode` y `data-accent` impresos desde el usuario (sin parpadeo).
2. **Sidebar** (inspirado en el prototipo): logo “PsicoCMS – Panel de gestión”, botón destacado “+ Nueva cita”, accesos directos: *Inicio, Citas, Calendario, Pacientes, Historias clínicas, Disponibilidad*; desplegables (estilo WordPress, estado abierto si contiene la ruta activa): **Blog** (Artículos, Categorías), **Mi web** (Datos generales, Sobre mí, Servicios, Especialidades, Planes y precios, Preguntas frecuentes, Imágenes, Frases públicas, Redes sociales, Temas visuales, SEO, Textos legales), **Configuración** (General/Perfil, Módulos de la web, Email y notificaciones, Protección de datos). Pie: **Ver mi web** (nueva pestaña), Ayuda, Cerrar sesión. Elemento activo resaltado.
3. **Topbar**: buscador global, campana de notificaciones, botón de apariencia (luna/sol), botón de Ayuda con tooltip en hover, avatar + nombre con menú (Mi perfil, Cerrar sesión). En móvil: sidebar off-canvas con botón hamburguesa y overlay.
4. Crear todos los componentes Blade, el JS núcleo y el CSS base de la sección 5 (incluidos tokens de modo oscuro y paletas, aunque el selector se active en Fase 14).
5. Las secciones aún no implementadas muestran un empty state “Próximamente” (se sustituyen en su fase).

**Aceptación**: navegación completa sin errores 404 en el menú; responsive (≥320 px); desplegables accesibles por teclado (`aria-expanded`).

### FASE 4 — Inicio, disponibilidad y gestión de citas ✅ COMPLETADA (pendiente de validación del usuario)
**4.1 Inicio** (`DashboardStatsService`): saludo “¡Hola, {nombre}!”, tarjetas (Citas hoy, Pacientes activos, Artículos publicados, Ingresos del mes = suma de `price` de citas no canceladas del mes), tabla “Próximas citas de hoy” (hora, paciente con iniciales, modalidad, acciones), tarjeta “Disponibilidad semanal” (rangos por día y modalidad), “Nuevas reservas web”, barras CSS de citas de las últimas 8 semanas. Datos reales desde el principio; para ver contenido se usa `DemoDataSeeder`. Empty states si no hay datos.

**4.2 Disponibilidad** (`/panel-psicologa/disponibilidad`):
- Fila superior en 2 columnas (apiladas en móvil): **Modo vacaciones** (toggle deslizante, guardado AJAX inmediato + toast; texto “Pausa todas las nuevas reservas, las citas existentes no se cancelan”) | **Periodos de vacaciones** (dos `<input type="date">`, nota opcional, botón Añadir; listado con borrar mediante modal; validación fin ≥ inicio y sin solape entre periodos; aviso no bloqueante si hay N citas dentro del periodo; empty state).
- Pestañas **Online | Presencial**, cada una con:
  - Tarjeta “Duración y descanso”: duración en minutos (input + chips rápidos 30/45/50/60), toggle de descanso + minutos, hora de entrada y hora de salida máxima (selects cada 15 min). Texto vivo: “Huecos de 60 min (50 de sesión + 10 de descanso)”.
  - Al cambiar duración/descanso/horario: modal de aviso antes de guardar. Tras guardar, se descartan los huecos que ya no encajan en la nueva rejilla, `needs_review = true` y se muestra un banner persistente “Revisa y vuelve a marcar tus huecos semanales (online y presencial)” hasta guardar la rejilla.
  - **Rejilla semanal** Lun–Dom × horas generadas por `AvailabilityService::grid()`: celdas-botón con `aria-pressed` que se marcan con un clic; clic en cabecera de día o de hora marca/desmarca la fila/columna; acción “Copiar lunes al resto de laborables”; botón **Guardar cambios** (PUT JSON) con toast.
- Tests unitarios del algoritmo de huecos (los dos ejemplos de la sección 5), periodos y modo vacaciones.

**4.3 Gestión de citas** (`/panel-psicologa/citas`):
- Listado paginado (15) con filtros GET: rango de fechas, modalidad, estado, origen, texto (paciente/teléfono/motivo); chips de estado y modalidad; cambio rápido de estado; acciones editar/eliminar (modal); empty state.
- Formulario crear/editar reutilizable (`panel/appointments/_form.blade.php` + `appointment-form.js`): paciente (nombre + teléfono; crea o vincula paciente por teléfono vía `PatientService`), modalidad, fecha, **selector de huecos libres** cargados de `/disponibilidad/huecos?modalidad&fecha&ignorar={id}`, opción “Hora personalizada (fuera de mi disponibilidad)” que sigue validando solapamientos, estado, origen, motivo, notas internas, precio (prellenado desde el plan).
- Toda escritura pasa por `AppointmentService` (transacción + bloqueo + `overlaps()`), error 422 claro “Ese horario se solapa con la cita de X a las HH:MM”.

**Aceptación**: los ejemplos 50+10 y 50+0 generan exactamente los huecos esperados; vacaciones bloquean huecos en el panel; no se pueden crear citas solapadas (tests de feature).

### FASE 5 — Calendario (FullCalendar) ✅ COMPLETADA (pendiente de validación del usuario)
**Tareas**
1. Vista `/panel-psicologa/calendario` como el prototipo: cabecera con mes, Hoy/‹/›, selector Mes/Semana/Día, botón “Nueva cita”; a la derecha panel “Agenda del día” (citas del día seleccionado, separador de descansos, empty state “No hay más citas programadas para hoy”).
2. `calendar.js`: FullCalendar con locale `es`, `firstDay: 1`, eventos de `/calendario/eventos?start&end` (JSON: título, inicio, fin, modalidad, estado, colores por modalidad: presencial verde, online azul; canceladas tachadas), periodos de vacaciones como eventos de fondo, `businessHours` desde la rejilla.
3. Clic en día → actualiza “Agenda del día”; clic en evento → modal de detalle (paciente, teléfono, motivo, estado; botones Editar, Cambiar estado, Eliminar, Ver paciente); clic en hueco vacío/“Nueva cita” → formulario de cita con fecha/hora precargadas.
4. Arrastrar y soltar para reprogramar → `PATCH /citas/{id}/mover` validado por `AppointmentService`; si falla, `info.revert()` + toast de error.
5. Estilos de FullCalendar sobrescritos en `public/panel/css/pages/calendar.css` usando las variables del panel (compatibles con modo oscuro).

**Aceptación**: las citas creadas en 4.3 aparecen; mover a un hueco ocupado revierte; vacaciones visibles; responsive (en móvil vista lista/día).

### FASE 6 — Blog (panel) y editor WYSIWYG
**Tareas**
1. `wysiwyg.js`: inicializa Jodit en todo `[data-wysiwyg]` (idioma `es`, barra simplificada: párrafo/H2/H3, negrita, cursiva, subrayado, listas, enlace, imagen, cita, tabla, deshacer/rehacer; altura 400). Subida de imágenes a `POST /panel-psicologa/editor/imagenes` (autenticado, validado, respuesta en formato Jodit). Se aplica a **todos** los textarea grandes del panel (blog, sobre mí, servicios, FAQ, historias, plantilla RGPD, textos legales).
2. Artículos: listado como el prototipo (tarjetas Total/Publicados/Borradores, buscador, filtros categoría/estado, tabla título+extracto, categoría, fecha, estado, acciones, paginación, empty state). Formulario: título, slug autogenerado editable (único), categoría (+ enlace a crear categoría), extracto, contenido Jodit (saneado), imagen destacada con previsualización, estado, fecha de publicación, meta description con contador de caracteres. Borrado con modal y eliminación de la imagen.
3. Categorías: CRUD (nombre, slug, descripción, nº de artículos); al borrar, los artículos quedan sin categoría (avisado en el modal). Categorías por defecto vía seeder.

**Aceptación**: CRUD completo; HTML malicioso (`<script>`, `onerror`) eliminado al guardar (test); imágenes servidas desde `storage`.

### FASE 7 — Pacientes
**Tareas**
1. Listado `/panel-psicologa/pacientes` como el prototipo: tarjetas (Total, Activos este mes, Consultas hoy), filtros (texto, estado, modalidad preferida, género) y tabla renderizada **por JS** desde `/pacientes/listado` (JSON paginado): al filtrar no se recarga la página, se muestra un skeleton de carga en la tabla y las filas se pintan con `el()`; búsqueda con debounce 300 ms; paginación AJAX; estado de filtros en la URL con `history.replaceState`; empty state.
2. Crear/editar paciente: teléfono obligatorio (normalizado, único, mensaje claro si ya existe con enlace a la ficha), nombre, apellidos, email, fecha de nacimiento, género, DNI, dirección, ciudad, CP, ocupación, contacto de emergencia, modalidad preferida, estado, enfoque/tipo de terapia, motivo de consulta, notas (Jodit).
3. Ficha del paciente (`show`) con pestañas: **Datos**, **Citas** (próximas y pasadas, botón “Nueva cita para este paciente”), **Historia clínica** (Fase 8), **Documentos** (Fase 9). Resumen: nº sesiones, última y próxima cita.
4. **Autocompletado** en el formulario de cita (`patient-autocomplete.js` + `/pacientes/buscar?q=`, máx. 8 resultados, navegación con teclado): al elegir, rellena nombre, teléfono y email; si no se elige ninguno, `AppointmentService` crea el paciente con los datos introducidos y vincula la cita.
5. Borrado de paciente con soft delete y modal de confirmación que indica cuántas citas e historias tiene.

**Aceptación**: crear cita con paciente nuevo lo crea y vincula; con teléfono existente “ 600 11 22 33 ” vincula al mismo paciente; filtros sin recarga (test del endpoint JSON).

### FASE 8 — Historias clínicas
**Tareas**
1. `/panel-psicologa/historias`: listado de pacientes con nº de entradas y fecha de la última; buscador; empty state.
2. Historia de un paciente (también como pestaña de la ficha): línea temporal de sesiones (fecha, título, extracto, adjuntos) y botón “Añadir nota de sesión”.
3. Formulario de entrada: fecha de sesión (hoy por defecto), cita vinculada (select de citas del paciente), título, contenido Jodit, **zona de arrastrar y soltar** para varias imágenes (jpg, png, webp) y PDF (máx. 10 MB c/u) con previsualización.
4. Adjuntos guardados en `storage/app/private/clinical/{patient_id}/` con nombre aleatorio; servidos solo por `ClinicalAttachmentController` autenticado (imágenes inline en un visor modal, PDF en nueva pestaña o descarga); borrado individual con modal.
5. Borrar una entrada borra sus adjuntos del disco.

**Aceptación**: un invitado recibe redirección al login al pedir un adjunto (test); no hay URL pública que exponga los ficheros.

### FASE 9 — Protección de datos (plantilla + PDF)
**Tareas**
1. `/panel-psicologa/configuracion/proteccion-datos`: editor Jodit con plantilla RGPD/LOPDGDD por defecto (seeder) y panel de **marcadores clicables** que se insertan en el cursor: `[nombre_paciente]`, `[dni_paciente]`, `[telefono_paciente]`, `[email_paciente]`, `[direccion_paciente]`, `[fecha]`, `[nombre_psicologa]`, `[num_colegiado]`, `[direccion_consulta]`, `[email_consulta]`. Botones Guardar y **Descargar plantilla vacía (PDF)** (marcadores → líneas para rellenar a mano).
2. Vista `resources/views/pdf/privacy.blade.php` (A4, cabecera con datos de la consulta, zona de firmas).
3. En la ficha del paciente: botón **Descargar documento de protección de datos** (PDF relleno) y checkbox “Documento firmado” que guarda `privacy_signed_at` (con badge en el listado).

**Aceptación**: los dos PDF se generan con acentos correctos (fuente DejaVu de dompdf) y sin marcadores sin sustituir.

### FASE 10 — Preguntas frecuentes (panel)
CRUD de FAQs (pregunta, respuesta con Jodit, activa), reordenación con arrastrar y soltar nativo (`sortable-list.js` → `PATCH /orden`) y botones subir/bajar como alternativa accesible; vista previa tipo acordeón; empty state.
**Aceptación**: el orden persiste; las inactivas no se publican.

### FASE 11 — Configuración de la información pública
Páginas bajo **Mi web** para editar todo lo introducido en el instalador y ampliarlo:
- **Datos generales**: nombre público, eslogan, nº colegiado, teléfono y email de citas, WhatsApp, dirección, ciudad, texto de horario, consulta para el mapa.
- **Sobre mí**: texto Jodit, foto de la psicóloga, formación/experiencia (lista repetible), años de experiencia.
- **Servicios** (CRUD con icono FA seleccionable, imagen, extracto, contenido, orden, activo), **Especialidades** (CRUD), **Planes y precios** (CRUD con modalidad, precio, duración, características, destacado).
- **SEO**: título y descripción por defecto, imagen para redes (OG), favicon/logo.
- **Textos legales**: política de privacidad (Jodit, plantilla por defecto) — necesaria para el consentimiento del formulario de reservas.
**Aceptación**: cada cambio se refleja en la web pública (tras Fase 16) y muestra toast de confirmación.

### FASE 12 — Temas visuales (motor + 5 temas)
**Tareas**
1. `ThemeManager`, `ThemeAssetController`, namespace `theme::`, view composer que comparte `$site`, `$modules`, `$social`, `$nav`, `$phrases` con las vistas del tema.
2. Estructura obligatoria de cada tema:
   ```
   themes/{slug}/
     theme.json   {name, slug, description, version, author, preview:{primary,secondary,background,font}, images:{slot: "img/…"}}
     views/layout.blade.php
     views/partials/{head-seo,header,nav,footer,contact-buttons,whatsapp-float}.blade.php
     views/sections/{hero,about,services,specialties,prices,how-start,stats,blog-latest,faq,booking,location,cta}.blade.php
     views/pages/{home,about,services,specialties,blog-index,blog-show,faq,booking,legal,404}.blade.php
     assets/css/{reset,fonts,styles,responsive}.css · assets/js/* · assets/img/* · assets/fonts/*
   ```
   Todas las secciones usan las mismas claves de datos, `phrase()` y `theme_image()`, por lo que cambiar de tema no requiere tocar datos.
   La estructura de bloques de cada tema sigue obligatoriamente la sección **3.1** (`tema-visual-base`).
3. **5 temas** (solo con las fuentes locales disponibles: Lexend, Montserrat, Castoro):
   - `calma` — réplica prácticamente idéntica de `tema-visual-base` (terracota `#976147`, Lexend + Castoro, mismas imágenes y animaciones). Se convierte el HTML estático en Blade dinámico, conservando secciones útiles (banner, terapias, sobre mí/características, servicios, cómo empezar, precios, estadísticas, blog, cita, footer) y descartando las que no aplican (p. ej. casos, promos si no hay datos).
   - `serenidad` — azul empolvado y blanco, Montserrat, hero centrado.
   - `salvia` — verde salvia natural, tarjetas redondeadas, hero con imagen a la derecha.
   - `lavanda` — lavanda/rosa suave, formas orgánicas, tono cálido.
   - `esencia` — editorial y minimalista, crema y negro, Castoro dominante.
   Los temas 2–5 parten del marcado del tema base y cambian paleta, tipografía y composición de secciones; cada uno es independiente (copiar carpeta = nuevo tema).
4. Panel **Temas visuales**: rejilla de tarjetas (mini maqueta CSS generada desde `preview` del manifiesto, o `screenshot.jpg` si existe), badge “Activo”, selector Landing/Multipágina. Clic → modal con **previsualización real** en iframe escalado (`/mi-web/temas/{tema}/previsualizar?modo=…`, autenticada, `noindex`) usando los datos de la psicóloga; botones “Activar este tema” y alternar modo. Botón destacado **“¿Quieres un diseño personalizado? Pídelo aquí.”** → `https://victorroblesweb.es/contacto` (`target="_blank" rel="noopener"`).

**Aceptación**: activar otro tema cambia la web al instante; añadir una carpeta nueva con `theme.json` válido aparece en el selector sin tocar código; los 5 temas renderizan home en ambos modos.

### FASE 13 — Gestión de imágenes
Slots estándar en `config('psicocms.image_slots')` (p. ej. `logo`, `favicon`, `hero`, `hero-secundaria`, `sobre-mi`, `servicios-fondo`, `como-empezar`, `estadisticas-fondo`, `cta-fondo`, `footer-forma`, `og`), cada tema declara su imagen por defecto en `theme.json`. `theme_image($key)` devuelve la subida (prioridad) o la del tema. Página **Imágenes**: tarjeta por slot con imagen actual (badge “Imagen de ejemplo” si es la del tema), tamaño recomendado, subir/reemplazar con previsualización, restaurar por defecto (modal). Validación: jpg/png/webp/svg (svg solo logo, saneado o rechazado si contiene script), máx. 4 MB.
**Aceptación**: subir una imagen la muestra en la web con cualquier tema; restaurar vuelve a la del tema.

### FASE 14 — Apariencia del panel y módulos activables
1. Botón luna/sol del topbar → **modal** con: selector Claro/Oscuro (tarjetas con mini vista) y **8 colores** (`config('psicocms.panel_palettes')`): Azul sereno `#3D5F8A` (actual), Salvia `#5E8B6E`, Lavanda `#7B6BA8`, Terracota `#B06A52`, Turquesa calma `#2F7F86`, Malva `#9E6582`, Índigo `#4A55A2`, Pizarra `#56657A`. Previsualización en vivo cambiando `data-mode`/`data-accent`; Guardar → `PATCH /configuracion/apariencia` en `users` (persiste tras logout/cerrar navegador/otro dispositivo). Toda la paleta deriva de `--color-primary` con `color-mix()`; revisar contraste en modo oscuro de todas las páginas (incluidos Jodit y FullCalendar).
2. **Módulos de la web** (`/configuracion/modulos`): toggles para Blog, Reservas online, Preguntas frecuentes, Servicios, Especialidades, Planes y precios, Botón flotante de WhatsApp, Mapa. Efectos: middleware `module:blog` (404 en rutas públicas), enlaces ocultos en la navegación, exclusión del sitemap, secciones ocultas en landing; si Reservas está desactivado, los CTA “Pide cita” pasan a Llamar/WhatsApp. En el sidebar del panel, la sección muestra un badge “Desactivado en la web”.
**Aceptación**: la elección de apariencia persiste tras logout; desactivar Blog devuelve 404 en `/blog` y lo oculta del menú público.

### FASE 15 — Frases, redes, email, perfil, buscador, ayuda
1. **Frases públicas**: rellenar `config/phrases.php` con todas las cadenas de los temas agrupadas (Navegación, Inicio, Sobre mí, Servicios, Precios, Blog, FAQ, Reservas, Contacto, Pie). Página con acordeón por grupo, filtro de búsqueda, valor por defecto como placeholder, botón restaurar por frase y Guardar. Repasar los 5 temas para que **ningún** texto fijo quede fuera de `phrase()`.
2. **Redes sociales**: Instagram, Facebook, LinkedIn, X, TikTok, YouTube, Doctoralia, Top Doctors (validación URL, icono FA de marca, vista previa).
3. **Email y notificaciones**: activar/desactivar, cuenta Gmail, contraseña de aplicación (cifrada, nunca se reimprime en el formulario), nombre del remitente, email destinatario (por defecto la misma cuenta). Mini tutorial paso a paso (activar verificación en dos pasos → `https://myaccount.google.com/apppasswords` → crear contraseña → pegarla). Botón **Enviar email de prueba** (AJAX) con errores traducidos a lenguaje sencillo.
4. **Perfil** (`/perfil` = Configuración > General, clic en avatar/nombre del topbar): nombre, apellidos, email y teléfono de acceso (aviso “Usarás estos datos para entrar”), avatar con previsualización; formulario aparte de cambio de contraseña (actual + nueva + confirmación).
5. **Buscador global**: en el topbar, sugerencias instantáneas (top 5 mezclado, debounce) y Enter → `/panel-psicologa/buscar?q=`. Página de resultados agrupada con contador e icono por tipo: **Accesos rápidos** (secciones del panel por palabras clave, p. ej. “vacaciones” → Disponibilidad), **Pacientes** (nombre/teléfono normalizado/email), **Citas** (paciente, motivo; entiende “hoy”, “mañana” y fechas dd/mm/aaaa), **Historias** (título/contenido con fragmento resaltado con `<mark>` escapado), **Blog**, **Servicios**, **FAQ**; acciones rápidas en cada resultado (ver ficha, nueva cita, editar). Empty state con sugerencias.
6. **Ayuda**: botón con hover/tooltip → `/panel-psicologa/ayuda`, tutorial en texto sencillo con índice y anclas: Primeros pasos, Disponibilidad y vacaciones, Citas y calendario, Pacientes, Historias clínicas, Protección de datos, Blog, Mi web (datos, temas, imágenes, frases, redes), Configuración (email, módulos, apariencia), Preguntas habituales.
7. **Notificaciones**: la campana muestra las reservas web con `seen_at` nulo (contador + desplegable), “Marcar como vistas”.
8. Botón **Ver mi web** en el sidebar (ya creado en Fase 3, verificar).

**Aceptación**: email de prueba llega con una cuenta Gmail real (prueba manual del usuario); búsqueda por teléfono con espacios encuentra al paciente.

### FASE 16 — Web pública
1. Controladores `Site\PageController`, rutas públicas de la sección 6, layout del tema activo. Portada según `index.html` e interiores según `interior.html` del tema base (sección **3.1**).
2. **Modo landing**: `/` renderiza todas las secciones activas en una página con scroll suave (`scroll-behavior: smooth` + offset del nav fijo) y navegación por anclas; `/sobre-mi`, `/servicios`, etc. redirigen 301 a `/#ancla`. Blog (listado y detalle) y textos legales siguen teniendo URL propia.
3. **Modo multipágina**: Inicio (landing reducida con lo imprescindible: hero, resumen sobre mí, servicios destacados, CTA, últimos artículos), Sobre mí, Servicios, Especialidades, Blog, Preguntas frecuentes, **Pide cita** (columna principal: reservas; columna secundaria: “¿Dónde estamos?” con dirección, mapa de Google embebido, teléfono, email y WhatsApp).
4. Botones de contacto en cabecera/hero/pie: Pedir cita, Llamar (`tel:`), WhatsApp (`https://wa.me/{numero}?text=…`), Email (`mailto:`); botón flotante de WhatsApp si el módulo está activo.
5. **SEO**: `<html lang="es">`, un único `h1` por página (corregir el `h1` del logo del tema base), títulos y meta descriptions por página, canonical, Open Graph/Twitter Card, JSON-LD (`Psychologist`/`MedicalBusiness` con dirección y teléfono, `Person`, `BreadcrumbList`, `FAQPage`, `BlogPosting`), `sitemap.xml` y `robots.txt` dinámicos (bloqueando `/panel-psicologa` y `/acceso-psicologa`), `loading="lazy"` + `width/height` en imágenes, `alt` descriptivos, `preload` de fuentes, página 404 del tema, `noindex` en vistas previas.
6. Página **Política de privacidad** desde Textos legales.

**Aceptación**: navegación completa en ambos modos con los 5 temas; validación HTML sin errores graves; Lighthouse SEO ≥ 95 en home.

### FASE 17 — Reservas públicas
1. Widget compartido `public/assets/shared/js/booking.js` (los temas solo aportan CSS para las clases `.booking__*`), incluido en la sección `booking` de cada tema:
   1. **Modalidad**: dos tarjetas Online / Presencial con duración y precio (desde `plans`).
   2. **Calendario mensual** propio en JS nativo: días con hueco seleccionables, resto deshabilitados, días de vacaciones marcados (“Vacaciones”), navegación de meses limitada al horizonte; datos de `GET /reservas/dias?modalidad&mes`.
   3. **Huecos** del día: chips de hora desde `GET /reservas/huecos?modalidad&fecha`.
   4. **Datos**: nombre, teléfono (obligatorio), email (opcional), motivo de la consulta, checkbox obligatorio de aceptación de la política de privacidad, campo honeypot anti-spam.
   5. Envío `POST /reservas` con estado de carga; errores inline; si el hueco se ocupó entre medias (409) se refrescan los huecos con mensaje claro.
2. Servidor (`Site\BookingController` + `AppointmentService`): throttle (60/min consultas, 10/hora envíos por IP), normaliza teléfono, `PatientService::findOrCreateByPhone` (origen `web`), cita `confirmada`, origen `web`, `seen_at` nulo (campana), revalidación de disponibilidad dentro de transacción.
3. **Modal de éxito** con resumen y botones **Añadir a Google Calendar** (enlace TEMPLATE con fechas UTC) y **Descargar .ics** (`/reservas/{token}.ics`).
4. **Email a la psicóloga** (`NewAppointmentMail`, plantilla HTML sencilla con datos de la cita y enlace al panel) si las notificaciones están activas; `afterResponse()` y `try/catch` con log: un fallo de correo nunca rompe la reserva.
5. Estados especiales: modo vacaciones activo o módulo de reservas desactivado → mensaje amable con Llamar/WhatsApp/Email.

**Aceptación** (tests de feature): reserva crea paciente + cita; teléfono con espacios reutiliza paciente; hueco ocupado → 409; día de vacaciones sin huecos; modo vacaciones sin días; email enviado con `Mail::fake()`.

### FASE 18 — Blog público y redes en el pie
1. `/blog`: artículos publicados paginados (9 por página), filtro por categoría (chips; `/blog/categoria/{slug}`), empty state. Respeta el módulo Blog.
2. `/blog/{slug}`: imagen destacada, categoría, fecha, tiempo de lectura, contenido, artículos relacionados (misma categoría), CTA a Pide cita, JSON-LD `BlogPosting`, OG con la imagen del artículo. Los borradores dan 404.
3. Pie de todos los temas: iconos de redes sociales configuradas (FA brands, `aria-label`, `target="_blank" rel="noopener"`), solo las que tengan URL.

**Aceptación**: paginación y filtro correctos; borradores invisibles; redes del panel aparecen en el pie de los 5 temas.

---

## 8. Optimizaciones propuestas (CLAUDE.md pide indicarlas)

Incluidas ya en este plan:
1. **Esquema de BD completo en la Fase 0** (como indica “Crea la base de datos” antes de las fases): evita migraciones de parcheo y permite que la Fase 4 (citas) use ya la tabla `patients` que la Fase 7 amplía.
2. **Componentes Blade + JS núcleo + tokens CSS (incl. modo oscuro) en la Fase 3**: todas las fases posteriores los reutilizan y la Fase 14 se reduce a conectar el selector.
3. **`AvailabilityService` como fuente única** para reservas públicas, formulario de citas y calendario: una sola lógica de huecos, descansos, vacaciones y solapamientos, cubierta por tests.
4. **Dependencia Fase 1/12/16**: el instalador necesita elegir tema antes de que existan → en Fase 1 se crean solo los manifiestos; las vistas de los temas se construyen en Fase 12 (necesarias para la previsualización), y la Fase 16 se centra en rutas, modos landing/multipágina, contacto y SEO.
5. **Previsualización de temas con mini maqueta CSS** desde `theme.json` (no depende de capturas de pantalla) + previsualización real en iframe.
6. **Email con `afterResponse()`**: no hace falta worker de colas en XAMPP.
7. **Sin Vite/npm**: menos piezas y fiel al stack nativo.
8. **Política de privacidad + consentimiento** en el formulario de reservas: no estaba explícito, pero es necesario por RGPD al recoger el motivo de consulta (dato de salud).

Sugeridas, **no incluidas** salvo que el usuario las apruebe:
- Email de confirmación/recordatorio al paciente si deja su email.
- Cifrado en reposo del contenido de las historias clínicas (cast `encrypted`). Contrapartida: la búsqueda global no podría buscar dentro del contenido y perder `APP_KEY` haría los datos irrecuperables.
- Copia de seguridad descargable (BD + uploads) desde el panel.
- Aviso legal y banner de cookies (solo necesario si se añaden cookies de analítica).

---

## 9. Verificación

**Automática** (al cierre de cada fase, `cd psicocms && php artisan test`):
- Unit: `Phone::normalize`, algoritmo de huecos (50+10 y 50+0), solapamientos, vacaciones, sustitución de marcadores RGPD.
- Feature: protección de **todas** las rutas `panel-psicologa*` (recorriendo el router), login con los 3 campos + throttle + recordarme, CRUD de citas con solapes, endpoint JSON de pacientes, autocompletado, adjuntos privados, saneado HTML, módulos desactivados → 404, flujo completo de reserva pública (con `Mail::fake()`), sitemap.

**Manual** (el usuario, tras cada fase, con `php artisan serve`):
1. Fase 1: borrar la BD y `installed.lock` → completar el asistente desde cero.
2. Configurar disponibilidad 50+10 online y 50+0 presencial → comprobar huecos en el formulario de cita del panel y (Fase 17) en `/pide-cita`.
3. Añadir un periodo de vacaciones y activar/desactivar el modo vacaciones → días bloqueados en ambos lados.
4. Reservar desde la web con teléfono “ 600 11 22 33 ” → paciente creado, cita en el calendario, campana con aviso, email recibido, enlace a Google Calendar correcto.
5. Recorrer el panel en móvil (DevTools 375 px) y en modo oscuro con varios colores.
6. Cambiar entre los 5 temas en landing y multipágina; desactivar Blog/Reservas y verificar la web.
7. Comprobar en DevTools que no hay errores de consola ni uso de `innerHTML` (`grep -r "innerHTML" psicocms/public psicocms/themes` debe salir vacío, salvo librerías vendorizadas).

**Regresión final**: suite completa en verde + checklist manual completo + `tareas.md` con todas las tareas marcadas.
