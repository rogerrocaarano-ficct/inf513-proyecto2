# Use Case: Buscar trámite de titulación

## Overview

**Use Case ID:** UC-015  
**Use Case Name:** Buscar trámite de titulación  
**Primary Actor:** Estudiante, Docente, Asistente administrativo, Director de carrera  
**Goal:** Encontrar un trámite de titulación por el nombre del estudiante o el título del proyecto de grado, para llegar a él sin recorrer listas  
**Trigger:** El usuario escribe un nombre o un título en la búsqueda del encabezado  
**Status:** Draft  

**Requirements:** [FR-038, NFR-001, NFR-004, NFR-005, NFR-009](../requirements.md)  
**Layers:** UI

## Preconditions

- El usuario inició sesión con el rol estudiante, docente, asistente administrativo o director de carrera.

## Main Success Scenario

1. El usuario escribe en la búsqueda del encabezado parte del nombre de un estudiante o del título de un proyecto de grado.
2. El usuario confirma la búsqueda.
3. El sistema busca, entre los trámites de titulación que el usuario puede ver (BR-001), los que coinciden por nombre del estudiante o por título del proyecto de grado (BR-002).
4. El sistema muestra los resultados con el estudiante, el título del proyecto de grado cuando ya lo tiene, la fase y si el trámite está en curso, concluido, abandonado o archivado.
5. El usuario elige un resultado.
6. El sistema muestra el trámite de titulación en la consulta que corresponde a su rol (BR-003).

## Alternative Flows

### A1: Texto demasiado corto

**Trigger:** El texto de búsqueda tiene menos de tres caracteres (step 3)  
**Flow:**

1. El sistema informa, con un mensaje en español, que la búsqueda necesita al menos tres caracteres, y conserva el texto.
2. El usuario completa el texto.
3. Use case continues at step 2.

### A2: Sin resultados

**Trigger:** Ningún trámite de titulación que el usuario puede ver coincide (step 4)  
**Flow:**

1. El sistema informa que no encontró trámites de titulación para ese texto.
2. Use case ends.

## Postconditions

### Success Postconditions

- El usuario vio los trámites de titulación que coinciden con su búsqueda entre los que puede ver, y abrió el que eligió.

### Failure Postconditions

_None — the use case changes no data._

## Business Rules

### BR-001: Trámites que encuentra cada rol

El asistente administrativo y el director de carrera encuentran todos los trámites de titulación. Un docente encuentra solo aquellos en los que es tutor, revisor o miembro del tribunal, vigente o anterior. Un estudiante encuentra solo los suyos (NFR-004). El administrador del sistema no busca trámites de titulación.

### BR-002: Coincidencia

Un trámite de titulación coincide cuando el nombre completo del estudiante o el título del proyecto de grado contiene el texto buscado, sin distinguir mayúsculas, minúsculas ni tildes. La búsqueda necesita al menos tres caracteres e incluye trámites en curso, concluidos, abandonados y archivados.

### BR-003: Consulta del resultado

Un resultado abre el trámite de titulación en la consulta que el rol del usuario ya tiene: el estudiante, la de su trámite (UC-013); el asistente administrativo, el detalle de UC-014; el director de carrera y el docente, el detalle del trámite con lo que su papel les permite ver.
