# Prompts

## Prompt 1
Crea un plan de implementación detallado para que un agente de IA (claude code con Claude Sonnet 5) sea capaz de desarrollar el proyecto y todas las funcionalidades descritas en el fichero @CLAUDE.md  Por ahora no generes código, solo crea el plan de implementación completo.

## Prompt 2
donde encuentro el @Plan_de_implementación_CLAUDE.md?

## Prompt 3
Ahora procede a hacer la FASE 1.

Contexto:
- Especificación en @CLAUDE.md (fuente de verdad funcional)
- Plan de implementación @Plan_de_implementación_CLAUDE.md (orden de ejecución obligatoria)
- Estado actual del sistema en @project-map.md (fuente de verdad de estado real).
- Debe seguir el plan de implemetación estríctamente sin replantearlo.

Jerarquía de conflicto:
1. Revisa @CLAUDE.md (que construir)
2. Revisa el @Plan_de_implementación_CLAUDE.md (como construirlo)
3. @project-map.md (estado actual del sistema)

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

## Prompt 4
tener en cuenta para la página web la estructura indicada en "tema-visual-base"

## Prompt 5
Ahora procede a hacer la FASE 2.

(Mismo bloque de contexto, jerarquía de conflicto, reglas de ejecución, reglas SDD, control de consistencias, alcance y salida esperada que el Prompt 3.)

## Prompt 6
Ahora procede a hacer la FASE 3.

(Mismo bloque de contexto, jerarquía de conflicto, reglas de ejecución, reglas SDD, control de consistencias, alcance y salida esperada que el Prompt 3.)

## Prompt 7
Ahora procede a hacer la FASE 4.

(Mismo bloque de contexto, jerarquía de conflicto, reglas de ejecución, reglas SDD, control de consistencias, alcance y salida esperada que el Prompt 3.)

## Prompt 8
Cambia la moneda por pesos colombianos

## Prompt 9
Ahora procede a hacer la FASE 5.

(Mismo bloque de contexto, jerarquía de conflicto, reglas de ejecución, reglas SDD, control de consistencias, alcance y salida esperada que el Prompt 3.)

## Prompt 10
Ahora procede a hacer la FASE 6.

(Mismo bloque de contexto, jerarquía de conflicto, reglas de ejecución, reglas SDD, control de consistencias, alcance y salida esperada que el Prompt 3.)

## Prompt 11
Ahora procede a hacer la FASE 7.

(Mismo bloque de contexto, jerarquía de conflicto, reglas de ejecución, reglas SDD, control de consistencias, alcance y salida esperada que el Prompt 3.)
