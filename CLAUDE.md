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
    - Rellenar la información básica de la psicologa, para la web (nombre y apellidos, frase gancho o eslogan, número de teléfono para citas, email para citas, servicios principales, sobre mi, tipos de especialidades que sabe o que hace, planes y precios (online y presencial), horarios y disponibilidad, dirección y lugar de consulta)
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

        - Gestionar todas las citas, pudiendo añadir nuevas, editar y eliminarlas. Se podrán visualizar en una sección con un listado paginado con diferentes filtros y también en el calendario


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


