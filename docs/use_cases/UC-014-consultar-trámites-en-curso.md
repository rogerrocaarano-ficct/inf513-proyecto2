# Use Case: Consultar trámites en curso

## Overview

**Use Case ID:** UC-014  
**Use Case Name:** Consultar trámites en curso  
**Primary Actor:** Asistente administrativo  
**Goal:** Ver la fase actual de todos los trámites de titulación en curso, para informar el estado de cualquiera cuando se lo consulten  
**Trigger:** El asistente administrativo solicita consultar los trámites de titulación en curso, por ejemplo cuando un estudiante o un docente pregunta por un trámite  
**Status:** Reviewed  

**Requirements:** [FR-024, NFR-005, NFR-009](../requirements.md)  
**Layers:** UI

## Preconditions

- El asistente administrativo inició sesión con el rol asistente administrativo.

## Main Success Scenario

1. El asistente administrativo solicita consultar los trámites de titulación en curso.
2. El sistema muestra todos los trámites de titulación en curso con el estudiante, su número de registro universitario, el título del proyecto de grado cuando ya lo tiene, el tutor, la fase actual y la fecha en que entró en ella, los que llevan más tiempo en su fase primero.
3. El asistente administrativo filtra la lista por fase.
4. El sistema muestra solo los trámites de titulación en curso de esa fase, con la cantidad.
5. El asistente administrativo elige un trámite de titulación.
6. El sistema abre el trámite de titulación en la consulta de trámite de titulación (UC-013).

## Alternative Flows

### A1: Sin filtro

**Trigger:** El asistente administrativo no filtra por fase (step 3)  
**Flow:**

1. El asistente administrativo elige un trámite de titulación de la lista completa.
2. Use case continues at step 6.

### A2: No hay trámites en curso

**Trigger:** No hay trámites de titulación en curso (step 2)  
**Flow:**

1. El sistema informa que no hay trámites de titulación en curso que mostrar.
2. Use case ends.

### A3: Ningún trámite en la fase elegida

**Trigger:** No hay trámites de titulación en curso en la fase elegida (step 4)  
**Flow:**

1. El sistema informa que no hay trámites de titulación en curso en esa fase.
2. El asistente administrativo elige otra fase o quita el filtro.
3. Use case continues at step 3.

## Postconditions

### Success Postconditions

- El asistente administrativo vio la fase actual de los trámites de titulación en curso y, si eligió uno, lo abrió en la consulta de trámite de titulación (UC-013).

### Failure Postconditions

_None — the use case changes no data._

## Business Rules

### BR-001: Trámites en curso

La lista muestra solo los trámites de titulación en curso: ni concluidos, ni abandonados, ni archivados (UC-001 BR-003, UC-012 BR-004). Los demás se encuentran con la búsqueda (UC-015).

### BR-002: Todos los trámites

El asistente administrativo ve todos los trámites de titulación en curso, de cualquier estudiante, tutor o tribunal, porque su rol informa el estado de cualquiera (NFR-005).

### BR-003: Solo consulta

La lista de trámites en curso no cambia nada; las acciones sobre un trámite de titulación se ofrecen al abrirlo, en su consulta (UC-013 BR-004).
