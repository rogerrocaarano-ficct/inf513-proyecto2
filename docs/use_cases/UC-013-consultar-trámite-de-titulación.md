# Use Case: Consultar trámite de titulación

## Overview

**Use Case ID:** UC-013  
**Use Case Name:** Consultar trámite de titulación  
**Primary Actor:** Estudiante, Docente, Asistente administrativo, Director de carrera  
**Goal:** Ver en una sola consulta el estado de un trámite de titulación, sus versiones del proyecto y sus observaciones, con las acciones que su rol le permite sobre él, para saber qué paso corresponde sin depender del asistente administrativo  
**Trigger:** El usuario abre un trámite de titulación desde su menú, una lista, la búsqueda (UC-015) o una notificación (UC-016)  
**Status:** Reviewed  

**Requirements:** [FR-013, FR-023, FR-040, NFR-004, NFR-005, NFR-008, NFR-009](../requirements.md)  
**Layers:** UI

## Preconditions

- El usuario inició sesión con el rol estudiante, docente, asistente administrativo o director de carrera.

## Main Success Scenario

1. El usuario abre un trámite de titulación.
2. El sistema verifica que el usuario puede ver ese trámite de titulación con su rol activo (BR-001).
3. El sistema muestra el trámite de titulación: el estudiante, el título del proyecto de grado cuando ya lo tiene, si está en curso, concluido, abandonado o archivado, la fase actual y las fases ya concluidas, cada una con la fecha en que el trámite entró en ella, el tutor, los miembros vigentes del tribunal evaluador y las pre-defensas y defensas formales programadas o realizadas con su fecha, hora y ambiente.
4. El sistema muestra la documentación de requisitos, cuando el rol activo puede verla (BR-006), y las versiones del proyecto del trámite de titulación, de la más reciente a la primera, cada una con su número, su fase, su estado y su fecha de presentación.
5. El sistema muestra las acciones que el rol activo del usuario puede ejecutar ahora sobre el trámite de titulación (BR-004).
6. El usuario elige una versión del proyecto.
7. El sistema muestra las observaciones de esa versión, cada una con su autor, su papel (revisor, tutor o miembro del tribunal) y su fecha, y permite descargar el archivo de la versión.
8. El usuario descarga el archivo de la versión del proyecto.

## Alternative Flows

### A1: El estudiante consulta su trámite

**Trigger:** El estudiante abre la consulta desde su menú, sin elegir un trámite de titulación (step 1)  
**Flow:**

1. El sistema abre su trámite de titulación más reciente y, si tiene trámites anteriores, reprobados o abandonados, la lista de ellos con su resultado y la fecha de conclusión o abandono, para abrir cualquiera.
2. Use case continues at step 3.

### A2: Trámite de titulación que el usuario no puede ver

**Trigger:** El rol activo del usuario no le permite ver ese trámite de titulación (step 2)  
**Flow:**

1. El sistema informa que el usuario no tiene acceso a ese trámite de titulación, sin mostrar ninguno de sus datos.
2. Use case ends.

### A3: Observaciones de la pre-defensa

**Trigger:** La pre-defensa del trámite de titulación ya se realizó con observaciones (step 3)  
**Flow:**

1. El sistema muestra, junto a la pre-defensa, las observaciones de cada miembro del tribunal con su autor y su fecha.
2. Use case continues at step 4.

### A4: Calificación o abandono

**Trigger:** El trámite de titulación concluyó con su defensa formal o quedó abandonado (step 3)  
**Flow:**

1. El sistema muestra la calificación conjunta y si concluyó aprobado o reprobado, sin las calificaciones de cada miembro del tribunal, o que quedó abandonado por inasistencia, con la fecha.
2. Use case continues at step 4.

### A5: El usuario ejecuta una acción

**Trigger:** El usuario elige una de las acciones que le ofrece el sistema (step 6)  
**Flow:**

1. El sistema abre el caso de uso de esa acción sobre el trámite de titulación (BR-004).
2. Use case ends.

### A6: El usuario solo consulta

**Trigger:** El usuario no elige una versión del proyecto ni una acción (step 6)  
**Flow:**

1. El usuario sale después de ver el estado del trámite de titulación.
2. Use case ends.

## Postconditions

### Success Postconditions

- El usuario vio el estado del trámite de titulación, sus versiones del proyecto con sus observaciones y autores y las acciones de su rol, y descargó el archivo que eligió.

### Failure Postconditions

_None — the use case changes no data._

## Business Rules

### BR-001: Quién ve un trámite de titulación

Con su rol activo, el asistente administrativo y el director de carrera ven todos los trámites de titulación. Un docente ve solo aquellos en los que hoy es tutor, revisor o miembro vigente del tribunal. Un estudiante ve solo los suyos, en curso, concluidos, abandonados o archivados (NFR-004). El administrador del sistema no consulta trámites de titulación.

### BR-002: Una sola consulta para todos los roles

Todos los roles ven el trámite de titulación en esta misma consulta y con el mismo contenido, salvo la documentación de requisitos (BR-006); lo que cambia según el rol activo son las acciones que se ofrecen (BR-004).

### BR-003: Versiones, observaciones y calificaciones

Se muestran todas las versiones del proyecto, desde el perfil de proyecto, con su fase y sus observaciones; ninguna se oculta ni se borra (NFR-008). Cada observación se muestra con su autor y su papel en el trámite de titulación. Nadie ve en esta consulta la calificación de cada miembro del tribunal, solo la calificación conjunta (UC-011 BR-003).

### BR-004: Acciones según el rol activo

La consulta ofrece solo las acciones que el rol activo del usuario puede ejecutar en ese momento sobre el trámite de titulación, según las reglas de cada caso de uso:

- estudiante: presentar una versión del proyecto (UC-004);
- revisor: evaluar el perfil de proyecto (UC-005);
- tutor: observar una versión del proyecto (UC-007);
- miembro del tribunal: revisar una versión del proyecto (UC-008), registrar la pre-defensa (UC-010) o la calificación (UC-011);
- director de carrera: asignar o cambiar el tutor (UC-002), asignar o reemplazar el tribunal evaluador (UC-006), programar o reprogramar la pre-defensa o defensa formal (UC-009);
- asistente administrativo: archivar el trámite de titulación (UC-012).

### BR-005: Rol activo

Un usuario con varios roles, como el director de carrera, que también es docente, ve y actúa según su rol activo; al cambiar de rol activo cambian los trámites de titulación que ve y las acciones que se le ofrecen.

### BR-006: Documentación de requisitos

La documentación de requisitos (boleta de inscripción, hoja de vida y carta de solicitud) es del estudiante: en esta consulta la ven y descargan solo el estudiante, el asistente administrativo y el director de carrera. Los docentes, como tutor, revisor o miembro del tribunal, no la ven.
