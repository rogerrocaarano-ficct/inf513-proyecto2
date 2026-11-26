# Use Case: Consultar notificaciones

## Overview

**Use Case ID:** UC-016  
**Use Case Name:** Consultar notificaciones  
**Primary Actor:** Estudiante, Docente  
**Goal:** Leer en el sistema las notificaciones de los trámites de titulación en los que participa y llegar al trámite que las originó, para enterarse de cada cambio sin depender del asistente administrativo  
**Trigger:** El usuario ve en el encabezado que tiene notificaciones sin leer, o solicita ver sus notificaciones  
**Status:** Draft  

**Requirements:** [FR-021, FR-022, NFR-004, NFR-009, C-005](../requirements.md)  
**Layers:** UI

## Preconditions

- El usuario inició sesión y tiene el rol estudiante o docente.

## Main Success Scenario

1. El usuario solicita ver sus notificaciones.
2. El sistema muestra sus notificaciones, las sin leer primero y de la más reciente a la más antigua, cada una con su mensaje, su fecha y hora y el trámite de titulación que la originó.
3. El usuario elige una notificación sin leer.
4. El sistema la marca como leída, con la fecha y hora, y abre el trámite de titulación que la originó en la consulta de trámite de titulación (UC-013).
5. El sistema actualiza en el encabezado la cantidad de notificaciones sin leer.

## Alternative Flows

### A1: Marcar todas como leídas

**Trigger:** El usuario marca todas sus notificaciones como leídas (step 3)  
**Flow:**

1. El sistema marca como leídas todas sus notificaciones sin leer, con la fecha y hora.
2. Use case continues at step 5.

### A2: Notificación ya leída

**Trigger:** El usuario elige una notificación que ya leyó (step 3)  
**Flow:**

1. El sistema muestra el trámite de titulación que la originó, sin cambiar la fecha de lectura.
2. Use case ends.

### A3: Trámite de titulación que ya no puede ver

**Trigger:** El usuario ya no participa en el trámite de titulación que originó la notificación, por ejemplo un tutor o un miembro del tribunal reemplazado (step 4)  
**Flow:**

1. El sistema marca la notificación como leída e informa que el usuario ya no tiene acceso a ese trámite de titulación.
2. Use case continues at step 5.

### A4: Sin notificaciones

**Trigger:** El usuario no tiene notificaciones (step 2)  
**Flow:**

1. El sistema informa que no tiene notificaciones.
2. Use case ends.

### A5: Notificación sin trámite de titulación

**Trigger:** La notificación no se originó en un trámite de titulación, como la designación del docente de taller de grado (UC-003) (step 4)  
**Flow:**

1. El sistema marca la notificación como leída, con la fecha y hora, y muestra su mensaje completo, sin abrir ningún trámite de titulación.
2. Use case continues at step 5.

## Postconditions

### Success Postconditions

- Cada notificación que el usuario abrió o marcó está leída, con la fecha y hora de lectura.
- La cantidad de notificaciones sin leer del encabezado coincide con las notificaciones sin leer del usuario.

### Failure Postconditions

- Ninguna notificación de otro usuario cambia, y ninguna notificación se borra.

## Business Rules

### BR-001: Solo sus notificaciones

Cada usuario ve y marca solo sus propias notificaciones (NFR-004).

### BR-002: Solo dentro del sistema

Las notificaciones se muestran solo dentro del sistema; no se envían por correo electrónico ni por otro canal (C-005).

### BR-003: Notificaciones permanentes

Una notificación no se borra; leerla solo registra la fecha y hora de lectura, la primera vez.

### BR-004: Acceso al trámite

Abrir una notificación no da acceso a un trámite de titulación en el que el usuario ya no participa (NFR-004).
