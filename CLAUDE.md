# Proyecto: PsicoCMS

## Rol del agente:
Desarrollador web con 12 años de experiencia

---

## Objetivo general:

Crear una aplicación web (CMS) para psicologos, donde puedan:
- Tener una web administrable
- Elegir entre varios temas visuales
- Tener un blog.
- Gestionar reservas de citas.
- Tener un panel de administración
- Gestionar pacientes.
- Gestionar historias clinicas.
- Gestionar disponibilidad y calendario.
- Administrar toda la información pública de la web

El objetivo es cubrir el flujo de trabajo completo de una psicologa independiente.

Todo se podrá administrar desde un panel privado

## Consideraciones generales:

Estas reglas aplican SIEMPRE a todas las fases y funcionalidades:

- Protección de rutas.
- Validación de solapamientos.
- Priorizar la sencillez, que todo sea intuitivo y de fácil entender.
- Priorizar buenas prácticas y seguridad.
- Mostrar mensajes de confirmación.
- Si no existen datos de una sección, mostar un "empty state" agradable.
- Usar Font Awesome (tenemos la fuente en concreto para usar en la carpeta "tema-visual.base/assets/fonts").
- Todas las acciones del dashboard requieren autenticación
- Todas las urls del panel deben comenzar por: /panel-psicologa
- Todas las funcionalidades deben ser totalmente funcionales.
- No romper funcionalidades anteriores.
- Mantener la consistencia visual en todo el dashboard.
- Mantener coherencia responsive.
- Priorizar la UX.
- Priorizar la reutilización de componentes

---

## Arquitectura general:

### Parte pública:
Incluirá:
- Homepage
- Sobre mi
- Servicios
- Especialidades
- Blog
- Preguntas frecuentes
- Sistema de reservas de citas
- Contacto

### Parte privadad - Dashboard:
Permitirá:
- Gestionar citas.
- Gestionar pacientes.
- Gestionar historias.
- Gestionar servicios.
- Gestionar blog.
- Gestionar preguntas frecuentes.
- Gestionar especialidades.
- Gestionar temas visuales.
- Gestionar imagenes.
- Gestionar configuración de la web.
- Gestionar disponibilidad.
- Gestionar frases públicas.
- Gestionar notificaciones por email.
- Gestionar redes sociales.
- Gestionar el perfil privado.


---

## Funcionalidades de la aplicación:

### FASE 1:
- Asistente de instalación donde se rellenan en un inicio los datos más importantes de la psicologa:
    - Crear la base de datos automáticamente con los datos de nuestro servidor y nuestra conexión.
    - Nombre y apellidos de la psicologa
    - Email, numero de teléfono y contraseña (para hacer el login con estos 3 datos, únicos y privados para la psicologa, no habrá multiples usuarios, ni registros, más allá de la instalación inicial).
    - Rellenar la información básica de la psicologa, para la web (nombre y apellidos, frase gancho o eslogan, número de colegiado, número de teléfono para citas, email para citas, servicios principales, sobre mi, tipos de especialidades que sabe o que hace, planes y precios (online y presencial), horarios y disponibilidad, dirección y lugar de consulta)
    - Subir una foto de la psicologa, preferiblemente sin fondo, indicarlo.
    - Todos estos datos luego serpan modificables y ampliables en el dashboard.
    - Selección de tema o plantilla visual (habrá 5 para elegir, básate en el que ya tenemos en la carpeta "tema-visual-base", ese será prácticamente idéntico, pero básate en él para crear más).
    - Cuando el asistente termine, llegaremos al dashboard de administración.

### FASE 2:
- Panel de administración privado:
    - Login seguro, con Email, número de teléfono y contraseña.

    - Será obligatorio introducir los 3 datos y debe existir la opción de persistir el login.

    - Usa el método más adecuado para el login y la autenticación segura pero que no se pase de complejo. Y que la contraseña esté bien cifrada.
    
    - Login de la psicologa en la url: /acceso-psicologa

### FASE 3:
- Dashboard en la url: /panel-psicologa (todas las urls de dentro del dashboard irán a partir de esta y todas requieren autenticación de la psicologa, al igual que cualquier acción que hagamos en el backend relacionado con el panel de administración)

- Layout estructura y menú del dashboard.

- El dashboard debe quedar muy simple, y debe tener un menú lateral izquierdo donde se agrupen las cosas de la gestión de la web pública y las configuraciones para no tener mucho lio. Ciertos elementos del menú serán desplegables (igual que en wordpress para agrupar cosas que tienen sentido que estén agrupadas) y las opciones más importantes para la gestión de la psicologa deben tener su elemento del menú para un acceso más rápido.

### FASE 4:
- Dentro del panel se podrá:
    - Página de inicio con un resumen de todo y estadísticas básicas (inicialmente con datos de prueba, cuando se completen el resto de las fases ya aparecerán datos reales.)
    - Configuración de disponibilidad de la profesional (online y presencial):
        - Debemos tener un campo de duración de las sesiones (en minutos) .
        Teniendo eso en cuenta:
        - Debemos tener un selector de hora de entrada y hora de sálida (máxima).
        - Debemos tener una semana de 7 días con la posibilidad de marcar las disponibilidad de horar marcándolas dando click.
        - Y un botón de guardar, para dejar asignada la disponibilidad que luego se usará en la parte pública para que los pacientes puedan reservar cita.
        - En esta sección también habrá un checkbox deslizante para marcar el "modo vacaciones" y así poder parar el sistema de citas.
        - Funcionalidad descanso entre sesiones tanto presencial como online y que ese tiempo se tenga en cuenta para los huecos de disponibilidad.

        El descanso entre sesiones debe ser configurable en el dashboard y se puede configurar, activar y desactivar en la sección de disponibilidad del dashboard.
        Cuando hayt tiempo de desacnao entre sesiones configurado y activado, ese tiempo se "suma" al tiempo dispobible de cada uno de los "huecos" disponbibles que hay para reservar por parte de los pacientes ( y se debe tener en cuenta en el formulario público de reservas y en las diferentes zonas del dashboard donde se usa la disponibilidad para añadir o modificar citar).

        Si por ejemplo mis citas online duran 50 minutos y configuro 10 minutos de descanso entre sesiones. Ahora mis huecos de disponibilidad para configurar son de 60 minutos. Por tanto los pacientes por ejemplo pueden reservar a las 9:00 am, a las 10:00 am, y así consecutivamente.

        Si mis citas presenciales presenciales duran 50 minutos y no tengo descanso entre sesiones. Ahora mis pacientes a nivel presencial pueden reservar a las 9:00 am, a las 9:50 am y asi consecutivamente.

        Y además cuando hago un cambio en mi duración de las sesiones o el tiempo de descanso entre sesiones, se debe avisar a la psicologa de que se debe volver a configurar y marcar sus huecos de disponibilida semanales tanto online como presenciales.

        - Funcionalidad para añadir periodo de vacaciones:
            - Se podrán añadir varios periodos de vacaciones ( fecha inicio o fecha final).
            - Los campos de fecha inicio y fecha final serán un selector de fecha de HTML5.
            - Los periodos de vacaciones se podrán borrar o añadir (tantos como queramos).
            - En el calendario de reservas público ( en el resto de sitios donde se use la disponibilidad de la psicologa) los días que coincidan con esos periodos de vacaciones estarán "bloqueados" para que no se pueda reservar citas en esos rangos de fechas.
            - Todos estos cambios se deben tener en cuenta en el formulario público de reservas y en las diferentes zonas del dashboard donde se usa la disponibilidad para añadir o modificar citas.
            - Esta funcionalidad irá aparte del "Modo vacaciones" general (que se puede activar o desactivar y ya está indicado).
            - El panel de "Modo vacaciones" y el panel de "Periodos de vacaciones" estarán uno al lado del otro.

        - Gestionar todas las citas, pudiendo añadir nuevas, editar y eliminarlas. Se podrán visualizar en una sección con un listado paginado con diferentes filtros y también en el calendario.

### FASE 5:
- Dentro del panel de administración:
    - Calendario con las citas del sistema de reservas de citas y posibilidad de añadir manualmente (tipo Google Calendar).  Si hay algún plugin o librería de javascript famosa y recomendable para esto, https://calendarjs.com/ puede ser una buena opción. Adáptalo a nuestro sistema.

### FASE 6:
- Dentro del panel de administración:
    - Gestión del Blog. Con su CRUD y a cada artículo se le puede subir una imagen y se le puede vincular a una categoría (que se pueden administrar, aparte de las creadas por defecto (crea las más adecuadas)).

    - Todos los campos de texto grandes en el dashboard deber tener incluido algún editor de texto wysiwyg para hacer agradable la edición (por ejemplo: https://xdsoft.net/jodit/).
    
    - Copia la librería dentro del proyecto, descargala del cdn. Esa info de la instalación y el uso general de la herramienta, la puedes conseguir desde aquí: https://xdsoft.net/jodit/docs/getting-started.html.

### FASE 7:
- Dentro del panel de administración:
    - Gestión de pacientes con los datos que pusieron al pedir cita (consultar la funcionalidad a desarrollar en la parte pública), con la posibilidad de añadir manualmente nuevos pacientes, editar los que hay para ampliar datos e información (mete los necesarios). El objetivo es que los pacientes al pedir cita mediante el sistema de reservas automáticamente se creen en este sistema de gestión, con los datos que ellos introdujeron, lo más importante y obligatorio es el número de teléfono, ya que se usará como identificador de los mismos (clave primaria), aparte del id que tenga ese paciente en la base de datos. Además, si por ejemplo la psicologa a agendado una cita con el paciente por teléfono, email, whatsapp o en persona. podrá añadir este paciente y su cita en forma manual.

    - Al crear una cita manualmente, implementar un buscador de pacientes en el campo del nombre del paciente y darle click que se rellenen los datos de la cita. Si el paciente al cual estamos dando cita no existe en la tabla de pacientes, crearlo a la vez que creamos la cita y vincular esa cita con ese paciente.

    - Que el buscador de pacientes sea asíncrono con el servidor (ajax), que cuando yo le de a filtrar no se recargue la página entera, sino que aparezca un efecto de carga en la tabla de pacientes y aparezcan ahí reactivamente los pacientes que estoy buscando.

### FASE 8:
- Dentrol del panel de administración:
    - Gestión de historias (seguimiento de sesiones del paciente con posibilidad de escribir y subir fotos o pdfs). El objetivo de esto es que cada paciente, tenga su historia o su ficha, y la psicologa pueda (después de cada sesión de terapía que teng con el paciente), escribir en su historia acerca de la sesión de hoy, subir fotos o documentos pdf escaneados con las anotaciones de la terapía del día.

### FASE 9:
- Dentrol del panel de administración:
    - Documentos de política de protección de datos (configuaración de plantilla y botón de generar pdf con los datos del paciente):
        - Página de configuración de la plantilla con editor wysywin (botón para descargar plantilla vacía en pdf).
        - En la página del detalle del paciente, a parte de poder meter mas información de él, tendremos un botón para descargar el pdf de la protección de datos y relleno con sus datos.

### FASE 10:
- Dentro del panel de administración:
    - Gestión de preguntas frecuentes para los pacientes.

### FASE 11:
- Dentro del panel de administración:
    - Configuración de toda la información que se muestra públicamente y que anteriormente se rellenó "provisionalmente" en el asistente.

### FASE 12:
- Dentro del panel de administración:
    - Gestión de temas con 5 plantillas diferentes, simplemente se podrán seleccionar y automáticamente se activará una nueva apariencia en la web. Debe haber un botón que ponga Quieres un diseño personalizado? Pídelo aquí. y que lleve aquí: https://victorroblesweb.es/contacto

    - Los temas se podrán seleccionar en formato landing (página larga) o multipáginas (diferentes secciones navegables.)

    - Al darle click al tema se podrá ver una pequeña previsualización con los datos que ha rellenado la psicologa en el asistente.

    - Las plantillas de los temas se guardarán en una carpeta de "themes" para fácilmente poder modificarlos o añadir nuevosy que sea todo muy plug and play.

### FASE 13:
- Dentro del panel de administración:
    - Gestión de imagenes, se podrán gestionar todas las imagenes estáticas que aparecen en la parte pública de la web. Habrá huecos en los temas visuales que aparecerán imagenes de prueba que aparecen por ejemplo en el "tema-visual-base", pero se podrán subir imagenes que a nivel programático tendrían prioridad para renderizarse en la web.

### FASE 14:
- Dentro del panel de administración:
    - Opción de tema claro y tema oscuro en el panel (al marcar uno de los dos, se quedará persistente, aunque me desloguee, cierre el navegador, etc):

        - Cuando le demos click al botón de tema claro/oscuro aparecerá una ventana modal, como selector de los colores más relacionados con la psicología, que haya 8 colores muy estéticos para que el usuario pueda seleccionar (uno será el azul que tenemos en el dashboard), para que actue como color principal del dashboard (que se adapte toda las paleta de colores a ese tema).
        
        - En la ventana modal de selección del tema claro/oscuro del dashboard, se podrá seleccionar si queremos un tema claro o oscuro y el color primario para el dashboard y se guardará y persistirá (se quedará persistente, aunque me desloguee, cierre el navegador, etc).

        - Casi todas las opciones de la web serán activables y desactivables (blog, sistema de reservas, faq, etc, porque habrá psicólogas que quizás no lo quieran).

### FASE 15:
- Dentro del panel de administración:

    - Crear una sección de configuración de "Frases públicas" donde se puedan configurar todos los strings o frases que aparecen por defecto en los temas visuales para que la psicologa los pueda personalizar.

    - Hacer las secciones de configuración de redes sociales donde la psicologa podrá poner todos los enlaces a las diferentes redes sociales para que aparezcan en la parte pública de la web.

    - Hacer la sección de "Email y notificaciones" (revisa la frase 16 donde explico la necesidad que tenemos, básicamente indicar un email de gmail para enviar un email a ese email con gmail con las nuevas reservas).

    - En el header del dashboard poder darle un click al nombre / avatar de la psicologa, para tener una sección donde podamos cambiar los datos privados de la psicologa (nombre, email interno/privado, teléfono interno/privado, poder modificar la contraseña y poder subir un avatar) - Sería lo mismo que la sección de configuración > General.

    - Hacer el buscador general que tenemos en el header del dashboard, topbar_search-label y que al buscar nos lleve a una página especial donde nos filtre y nos encuentre las coincidencias en citas, pacientes, historias, blog, etc (se creativo y haz algo muy útil para el usuario).

    - En el botón Ayuda del header del dashboard, que tenga un hover y que lleve a una sección donde explique con un tutorial general en texto y de forma sencilla de entender como funciona el dashboard de PsicoCMS completo.

    - Añadir un botón para ir a ver la parte pública de la web en la barra lateral.

### FASE 16:
- En la parte pública:

    - Tendremos la información de la psicologa.
    - Botones para pedir cita, llamar, contactar por whatsapp o mandar email.
    - Básate en la estructura de "tema-visual-base" (que tiene todas las opciones que una web de este tipo podría llegar a tener), quédate con las secciones necesarias para nuestro caso.
    - Si la psicologa elige el tema en modo landing, será toda la web en una sola página con scroll suavisado, sino, la web tendrá secciones bien diferenciadas:
        - Inicio con una landing page agradable y con los datos más estríctamente necesarios (básate en el tipo de web de la carpeta "tema-visual-base"), en una url.
        - Sección Sobre mi - en otra url.
        - Sección Servicios - en otra url.
        - Sección Blog - en otra url.
        - Sección Preguntas frecuentes - en otra url y quizas incluida.
        - Sección Pide cita - en otra url (dentro, en un lado tendremos toda la parte de hacer reservas, y en otro lado, con algo de menos importancia, tendremos un ?Dónde estamos?, con la dirección y un mapa de google maps con ella, además del número de teléfono y datos de contacto).

### FASE 17:
- En la parte pública:
    - Sección de reservas de citas:
        - Selector para elegir si la cita es presencial u online ( en función de eso y según la disponibilidad que la psicologa tenga configurada para sus citas presenciales y su disponibilidad online, el calendario siguiente aparecerá adaptado. Las disponibilidad que la psicologa va a tener online y presenciales son diferentes y podrá configurarlas por separado).
        - Calendario para seleccionar el dia (según las disponibilidad que tenga la psicologa configurada, ella podrá seleccionar los días de disponibilidad y horarios semanales).
        - El paciente podrá rellenar su nombre, número de teléfono y motivo de la consulta ( con base a esto se le creará su "ficha de paciente" cuya claver primaria o identificador principal será el número de teléfono, sin los espacios por delante y por detrás y espacios entre números, es decir el número todo junto, si el paciente no lo rellena así, se limpiará programáticamente. Aparte el paciente tendrá su id único en la base de datos).
        - Ventana modal de consulta agendada correctamente con botón de agendar en su calendario de google calendar por ejemplo (hazlo si hay una forma simple de hacerlo sin necesidad de usar apis).
        - Enviar un email a la psicologa avisando de que tiene una nueva cita ( si tiene configuradas las notificaciones por email con un correo de gmail, en la sección de configuración tener un formulario con los datos que necesitas y un mini tutorial para explicar a la psicologa como conseguirlos y poder hacer un envio de email sencillo con phpmailer / mailer de laravel a su propio correo).

### FASE 18:
- En la parte pública:

    - Sección de blog con los artículos paginados y su correspondiente filtrado por categorías (si la psicologa tiene actividad esta sección de blog, se podrá activar o desactivar desde el dashboard, al igual que tendrá un CRUD de artículos).
    - Botones de redes sociales en el footer (configurables las diferentes redes en el panel de administración)




## Stack de tecnología:
- HTML 5
- CSS3 (nativo, básate en el código de la carpeta "tema-visual-base")
- JavaScript (nativo, sin frameworks)
- Lenguaje de programación backend: PHP
- Framework para PHP: Laravel
- Base de datos MySQL 
- Aplicación web monolítica con la arquitectura de Laravel


## Preferencias generales:
- Todos los textos visibles en la web deben estan en español.
- El agente debe comunicarse en español
- Usa todas las imagenes de stock de "tema-visual-base" como plantillas.
- Usar imagenes para editar o cambiar la sección del dashboard

## Preferencias de diseño para la parte pública:
- Básate en el diseño de la carpeta "tema-visual-base"


## Preferencias de diseño para la parte privada:
- Crea un diseño de un dashboard visualmente agradable, sencillo de entender y muy intuitivo para una psicolaga, que no son usuarios avanzados en informática ( ten en cuenta todas las funcionalidades e inspírate mucho en la carpeta "dashboard-design" que tienes en la raiz del proyecto, dentro de esa carpeta tienes un prototipo de diseño, puede tener fallos, pero la idea y el concepto es muy similar a lo que necesitamos, hay diferentes pantallas diseñadas para insperarte en ellas e intentar imitarlas y mejorarlas. En el diseño hay ciertos datos que están mal, como el nombre del cms, algunos textos en ingles, etc, ten criterio y ten muy en cuenta las funcionalidades descritas en este documento)

---

## Preferencias de estilos:
- Colores, cada tema visual puede tener diferentes,el "tema-visual-base" ya tiene los suyos y los del panel de administración los tienes en la carpeta "dashboard-design".
- Uso de medidas en rem, usando un font-size base de 10px.
- Uso de HTML5 y CSS3 nativo.
- Uso de buenas prácticas de maquetación CSS, y si es necesario utiliza flexbox y css grid layout.
- La web debe ser responsivo, tanto en la parte pública, como en el dashboard.

---


## Preferencias de código:
- No mezcles el código css entre los diferentes componentes, ten bien separado para cada tema visual y para el dashboard. Si puedes tenerlo en una carpeta de css, como se hace en la carpeta "tema-visual.base", mucho mejor.
- La parte pública debe estar completamente optimizada para SEO ( a nivel codigo y buenas prácticas)
- Usa siempre let o const, y no uses nunca var.
- No uses alert, confirm o prompt, todo el feedback debe ser visual en el dom.
- Toda alerta o ventana modal que aparezca debe tener el mismo estilo que la web.
- No uses innerHTML, todo el contenido debe ser insertado con appendChild o previamente creando un elemento con document.createElement.
- Cuidado con olvidad prevener el default en los eventos submit o click.
- Prioriza el código legible y mantenible
- Prioriza el código facil y sencillo
- Si el agente duda, que revise las especificaciones del proyecto y si no que pregunte.

## Estructura de archivos:
- Carpeta "tema-visual-base", es una maquetación web de una de las plantillas o temas visuales de la web
- Carpeta "dashboard-design, contiene una imagenes con un diseño provisional para el dashboard.
- CLAUDE.md
- Carpeta para el proyecto laravel, usa la estructura de archivos más adecuada para proyectos de php-laravel

---

## Fases de desarrollo:
- Estudia las características del proyecto
- Crea la base de datos
- Sigue las fases del desarrollo, especificadas en la sección de funcionalidades de este fichero CLAUDE.md, y para trabajar después de cada fase para poder pobrar lo que has desarrollado, corregir y mejorar algo si es necesario y poder continuar.
- Si crees que se puede optimizar, indícamelo en el plan de implementación.

---

## Otras consideraciones:
- Guarda el plan de implementación en un fichero plan-implementación.md en la raiz del proyecto.
- Guarda las tareas y su estado en un fichero tareas.md en la raiz del proyecto y cada vez que se cumpla una, modifícalo para actualizarlas.
- Guarda cada uno de los prompt nuevos que haga en un fichero prompts.md en la raiz del proyecto (todos ordenados uno detras del otro dentro del fichero), cada vez que yo haga un prompt aparte, guardalo ahí.

## Modo de implementacion:
- Solo código, mínimos comentarios, el código ya debe ser autoexplicativo.
- No expliques que hace el código en el chat del agente.
- Responde en español si preguntas, pero en prompts usa Ingles/español.
- Si hay ambiguedad, asume la dicisión más simple que no rompa algo.


