# -> nivel de sistema (documentación completo)
## -> módulos / bloques grandes de funcionalidades (Autenticación, Blog, Pacientes, Reservas, etc)
### -> Funcionalidades dentrol de un módulo
bullets -> requisitos concretos

La estructura de un fichero de agentes(AGENTS.md o CLAUDE.md)
- Estructura dentro del fichero CLAUDE.md

SDD híbrido: Mismas reglas del SDD tradicional, pero combina ingenieria de prompts y vide code descriptivo, igualemnte poniendo reglas y límites en los prompts e indicaciones en el fichero de agentes. El resultado es identico si explicas todo bien. Más fácil y fluido de hacer


SDD estrícto: Lista de funcionalidades más escueta pero más ordenada, a veces quizas con menos contexto de como debe quedar la app, pero si con más seguridad a nivel de que sí y que no puede hacer el agente con el código que genere.

La estructura de uns SPEC:

## Funcionalidad: Nombre de la feature

### Objetivo: 
qué problema resuelves

### Entradas
- Campo 1
- Campo 2
- Campo 3

### Salidas
- Resultado esperado

### Reglas
- Restricciones
- Validaciones

### Comportamiento
- Paso 1
- Paso 2
- Paso 3


Prompt 
- Crear planes de implementación

Crea un plan de implementación detallado para que un agente de IA (claude code con Claude Sonnet 5) sea capaz de desarrollar el proyecto y todas las funcionalidades descritas en el fichero @CLAUDE.md. Por ahora no generes código, solo crea el plan de implementación completo.

- Prompt ente fases:

He terminado la FASE 4 del fichero @CLAUDE.md

Ahora procede a hacer la FASE 5.

Contexto:
- Especificación en @CLAUDE.md (fuente de verdad funcional)
- Plan de implementación @Plan_de_implementación_CLAUDE.md (orden de ejecución obligatoria)
- Estado actual del sistema en @project-map.md (fuente de verdad de estado real).
- Debe seguir el plan de implemetación estríctamente sin replantearlo.

Jerarquía de conflicto:

1. Revisa  @CLAUDE.md (que construir)
2. Revisa el @Plan_de_implementación_CLAUDE.md (como construirlo)
3. @project-map.md  (estado actual del sistema)


Reglas de ejecución:

- Consulta primero @project-map.md antes de empezar cualquier tarea
- Ejecuta las tareas en orden exacto, sin saltalte ningu.
- No entres en modo de planificación, análisis global sin rediseño.
- No realices pruebas tu mismo: implementa y avísame cuando este listo para validar.
- Solo pregunta si existe un bloqueo técnico real que impida continuar.

Reglas SDD obligatorias

- Después de cada cambio relevante, actualiza el @project-map.md
- Cambios realizados
- Nuevos módulos /rutas /entidades
- Decisiones técnicas tomadas.
- Impacto en el sistema

Control de consistencias:
- No inventes estado en @project-map.md.
- Si no hay discrepancias entre código y mapa, el código tiene prioridad y el map debe corregirse.

Alcance:

- Manten los cambios dentro de la fase actual.
- No modifiques funcionalidades fuera del scope de la fase que estamos desarrollando, salvo dependencia directa.

Salida esperada:
- Marca cada sub-tarea como completada.
- Al finalizar la fase:
    - Resumen de lo implementado.
    - Sincroniza completamente @project-map.md con el estado real del sistema.
    - Marca la fase como completada en el @project-map.md, en las tareas y en el @Plan_de_implementación_CLAUDE.md



Cómo funciona el flujo de trabajo con el SDD y las fases de desarrollo:

- Planificación.
- Ejecutar prompt entre tareas
- Probar resultado.
- Correcciones.
- Mandar al agente a corregir o mejorar
- Volver a ejecutar prompt entre fases



Cómo hacer cambios y modificaciones en un proyecto:

Prompts para exprimir al agente de IA al máximo



Diferentes opciones para hacer SDD.