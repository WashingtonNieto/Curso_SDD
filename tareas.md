# Tareas — PsicoCMS

Leyenda: `[ ]` pendiente · `[x]` completada

## FASE 0 — Preparación y base de datos ✅ (pendiente de validación del usuario)
- [x] 0.1 Crear `plan-implementación.md`, `tareas.md`, `prompts.md` (y `project-map.md`)
- [x] 0.2 Activar extensiones `gd`, `zip`, `intl` en `D:\xampp\php\php.ini`
- [x] 0.3 Crear proyecto Laravel 12 en `psicocms/` + `barryvdh/laravel-dompdf` + `mews/purifier`
- [x] 0.4 Configuración: locale `es`, timezone `Europe/Madrid`, drivers file/sync, `lang/es/*`
- [x] 0.5 Eliminar Vite del esqueleto
- [x] 0.6 Vendorizar Font Awesome, Jodit y FullCalendar; autoalojar Manrope
- [x] 0.7 Migraciones, modelos, factories y seeders completos
- [x] 0.8 `config/psicocms.php` y `config/phrases.php`
- [x] 0.9 `Support\Phone`, `SettingsService`, helpers, `SecurityHeaders`, rutas separadas
- [x] 0.10 Tests unitarios de `Phone::normalize`

## FASE 1 — Asistente de instalación ✅ (pendiente de validación del usuario)
- [x] 1.1 Middleware `EnsureInstalled` y `RedirectIfInstalled`
- [x] 1.2 Creación automática de `.env` y `APP_KEY` si faltan
- [x] 1.3 Asistente multipaso
  - [x] 1.3.1 Bienvenida y requisitos
  - [x] 1.3.2 Base de datos (crear BD, `.env`, migrar, `BaseDataSeeder`)
  - [x] 1.3.3 Cuenta de acceso
  - [x] 1.3.4 Datos públicos
  - [x] 1.3.5 Horarios
  - [x] 1.3.6 Foto
  - [x] 1.3.7 Tema
  - [x] 1.3.8 Finalizar
- [x] 1.4 Manifiestos `themes/{slug}/theme.json` de los 5 temas

## FASE 2 — Login seguro ✅ (pendiente de validación del usuario)
- [x] 2.1 `GET|POST /acceso-psicologa` (guest): email, teléfono y contraseña obligatorios, interruptor “Mantener la sesión iniciada”, mostrar/ocultar contraseña
- [x] 2.2 Lógica: teléfono normalizado, búsqueda por email, teléfono + `Hash::check`, mensaje genérico, `Auth::login($user, $remember)`, regeneración de sesión
- [x] 2.3 `RateLimiter`: 5 intentos/minuto por email+IP con segundos restantes
- [x] 2.4 Logout por POST con invalidación de sesión y regeneración de token; invitados redirigidos a `/acceso-psicologa`
- [x] 2.5 Diseño del acceso coherente con el panel (tarjeta + imagen lateral del tema base)
- [x] Tests de feature (login, teléfono/contraseña incorrectos, campos vacíos, throttle, recordarme, logout) y test de protección de rutas `panel-psicologa*`

## FASE 3 — Layout y menú del panel ✅ (pendiente de validación del usuario)
- [x] 3.1 Layout `panel/layout.blade.php` con `data-mode` y `data-accent` del usuario
- [x] 3.2 Sidebar: marca, “+ Nueva cita”, accesos directos, desplegables Blog / Mi web / Configuración, pie (Ver mi web, Ayuda, Cerrar sesión), elemento activo
- [x] 3.3 Topbar: buscador, notificaciones, apariencia, Ayuda con tooltip, avatar con menú; sidebar off-canvas en móvil
- [x] 3.4 Componentes Blade, JS núcleo y CSS base (con tokens de modo oscuro y paletas)
- [x] 3.5 Secciones no implementadas con empty state “Próximamente”
- [x] Test de navegación por todo el menú

## FASE 4 — Inicio, disponibilidad y gestión de citas ✅ (pendiente de validación del usuario)
- [x] 4.1 Inicio con `DashboardStatsService`: saludo, tarjetas (citas hoy, pacientes activos, artículos publicados, ingresos del mes), próximas citas de hoy, disponibilidad semanal, nuevas reservas web, barras de las últimas 8 semanas, empty states
- [x] 4.2 Disponibilidad
  - [x] Modo vacaciones (interruptor AJAX + toast) y periodos de vacaciones (añadir, borrar con modal, sin solapes, fin ≥ inicio, aviso si hay citas) uno al lado del otro
  - [x] Pestañas Online / Presencial con duración (chips 30/45/50/60), descanso activable, hora de entrada y salida, texto vivo de huecos
  - [x] Aviso modal al cambiar el horario; poda de huecos que ya no encajan; `needs_review` y banner persistente
  - [x] Rejilla semanal clicable (celdas, filas, columnas), “Copiar lunes al resto de laborables”, guardar por PUT JSON
  - [x] `AvailabilityService` completo (fuente única: rejilla, huecos por fecha, vacaciones, solapes, antelación y horizonte)
  - [x] Tests del algoritmo de huecos (50+10 y 50+0), periodos y modo vacaciones
- [x] 4.3 Gestión de citas
  - [x] Listado paginado (15) con filtros (fechas, modalidad, estado, origen, texto), badges, cambio rápido de estado, editar/eliminar con modal, empty states
  - [x] Formulario reutilizable crear/editar con paciente (crea o vincula por teléfono), modalidad, fecha, huecos libres por AJAX, hora personalizada, estado, origen, precio prellenado, motivo y notas
  - [x] `AppointmentService` (transacción + bloqueo + solapes) y `PatientService::findOrCreateByPhone`
  - [x] Tests de feature (creación, vinculación por teléfono, solapes, vacaciones, edición, estados, borrado, filtros, endpoint de huecos)

## FASE 5 — Calendario ✅ (pendiente de validación del usuario)
- [x] 5.1 Vista `/panel-psicologa/calendario` como el prototipo: título del periodo, Hoy/‹/›, Mes/Semana/Día (+ Lista), “Nueva cita” y panel “Agenda del día” con separadores de descanso y empty state
- [x] 5.2 `calendar.js` con FullCalendar (`es`, lunes primero), eventos de `/calendario/eventos`, colores por modalidad, canceladas tachadas, vacaciones como fondo y `businessHours` desde la rejilla
- [x] 5.3 Clic en día → agenda; clic en cita → modal de detalle (Editar, Cambiar estado, Eliminar, Ver paciente); clic en hueco vacío (semana/día) o “Nueva cita” → formulario con fecha/hora precargadas
- [x] 5.4 Arrastrar y soltar → `PATCH /citas/{id}/mover` validado por `AppointmentService::move`; si falla se revierte con toast de error
- [x] 5.5 Estilos de FullCalendar en `pages/calendar.css` con las variables del panel (modo oscuro)
- [x] Tests de feature del calendario

## FASE 6 — Blog (panel) y editor WYSIWYG
- [ ] Pendiente

## FASE 7 — Pacientes
- [ ] Pendiente

## FASE 8 — Historias clínicas
- [ ] Pendiente

## FASE 9 — Protección de datos
- [ ] Pendiente

## FASE 10 — Preguntas frecuentes
- [ ] Pendiente

## FASE 11 — Configuración de la información pública
- [ ] Pendiente

## FASE 12 — Temas visuales
- [ ] Pendiente
- [ ] Los 5 temas siguen la estructura de bloques de `tema-visual-base` (plan, sección 3.1)

## FASE 13 — Gestión de imágenes
- [ ] Pendiente

## FASE 14 — Apariencia del panel y módulos
- [ ] Pendiente

## FASE 15 — Frases, redes, email, perfil, buscador, ayuda
- [ ] Pendiente

## FASE 16 — Web pública
- [ ] Pendiente
- [ ] Portada según `index.html` e interiores (aside + main) según `interior.html` de `tema-visual-base`

## FASE 17 — Reservas públicas
- [ ] Pendiente

## FASE 18 — Blog público y redes en el pie
- [ ] Pendiente

## Ajustes solicitados
- [x] Prompt 8: moneda en pesos colombianos (COP) en todo el sistema
