# Use Case: Presentar versión del proyecto

## Overview

**Use Case ID:** UC-004  
**Use Case Name:** Presentar versión del proyecto  
**Primary Actor:** Estudiante  
**Goal:** Subir una nueva versión del proyecto de grado en la fase actual de su trámite de titulación, para que quienes la revisan la reciban sin que el estudiante vaya a la dirección de carrera  
**Trigger:** El estudiante decide presentar una nueva versión de su proyecto de grado: el perfil de proyecto, la versión que subsana un rechazo u observaciones, o la versión final para la defensa formal  
**Status:** Reviewed  

**Requirements:** [FR-005, FR-009, FR-022, NFR-004, NFR-008, NFR-009, NFR-013, C-004, C-014](../requirements.md)  
**Layers:** UI

## Preconditions

- El estudiante inició sesión y tiene el rol estudiante.

## Main Success Scenario

1. El estudiante solicita presentar una nueva versión de su proyecto de grado.
2. El sistema muestra la fase actual de su trámite de titulación y su última versión del proyecto con su estado y sus observaciones.
3. El estudiante adjunta el archivo de la versión y, en la fase evaluación de perfil, ingresa el título del proyecto de grado (BR-007).
4. El estudiante confirma la presentación.
5. El sistema verifica que la fase actual del trámite de titulación admite una nueva versión (BR-002), que un perfil de proyecto tiene título y que el archivo cumple el formato exigido.
6. El sistema registra la versión del proyecto con el número siguiente, la fase actual, el título que fija BR-007, el archivo, la fecha y hora y el estado en revisión; en la fase evaluación de perfil, con el docente de taller de grado como revisor.
7. El sistema notifica a quienes revisan la versión (BR-004) que una nueva versión del proyecto espera su revisión.
8. El sistema muestra la confirmación con el número y el estado de la versión del proyecto.

## Alternative Flows

### A1: No hay docente de taller de grado designado

**Trigger:** La versión es un perfil de proyecto y no hay un docente de taller de grado designado (step 6)  
**Flow:**

1. El sistema registra el perfil de proyecto en revisión, sin revisor.
2. El sistema informa al estudiante que el perfil de proyecto queda pendiente de revisión hasta que el director de carrera designe al docente de taller de grado.
3. Use case continues at step 8.

### A2: Versión para la defensa formal

**Trigger:** El trámite de titulación está en la fase defensa formal (step 6)  
**Flow:**

1. El sistema registra la versión del proyecto con el número siguiente, la fase defensa formal, el título del proyecto de grado, el archivo, la fecha y hora y el estado presentada.
2. El sistema notifica al tutor y a los miembros del tribunal que hay una nueva versión del proyecto para la defensa formal.
3. Use case continues at step 8.

### A3: La fase no admite una nueva versión

**Trigger:** La fase actual del trámite de titulación no admite una nueva versión (step 5)  
**Flow:**

1. El sistema informa al estudiante el motivo: el trámite de titulación espera la asignación de su tutor, su última versión sigue en revisión, su perfil de proyecto o su versión de la fase pre-defensa ya fue aprobada y espera el siguiente paso, su defensa formal ya se realizó, su pre-defensa no dejó observaciones que subsanar, o no tiene un trámite de titulación en curso.
2. Use case ends.

### A4: Perfil de proyecto sin título o archivo que no cumple el formato

**Trigger:** Falta el título de un perfil de proyecto o falta el archivo, o el archivo no es PDF o supera los 20 MB (step 5)  
**Flow:**

1. El sistema señala el dato faltante o el motivo por el que el archivo no cumple el formato, con un mensaje en español, y conserva lo ingresado.
2. El estudiante completa el título o adjunta un archivo correcto.
3. Use case continues at step 4.

## Postconditions

### Success Postconditions

- Existe una nueva versión del proyecto del trámite de titulación con el número siguiente, la fase en que se presentó, su título, su archivo y la fecha y hora de presentación.
- En las fases evaluación de perfil y pre-defensa la versión está en revisión; en la fase defensa formal está presentada.
- Quienes revisan la versión (BR-004) tienen una notificación de la nueva versión del proyecto.
- Las versiones anteriores del proyecto se conservan sin cambios, con sus observaciones.

### Failure Postconditions

- No se registra una versión del proyecto sin su archivo, ni se guarda un archivo sin su versión del proyecto.
- Las versiones anteriores del proyecto y el estado del trámite de titulación no cambian.

## Business Rules

### BR-001: Formato del archivo

Cada versión del proyecto es un archivo PDF de hasta 20 MB (C-004). El archivo se guarda en el almacenamiento de archivos del servidor y ninguna versión del proyecto se borra mientras el trámite de titulación exista (C-014, NFR-008).

### BR-002: Cuándo se admite una nueva versión

El estudiante presenta una nueva versión solo en su trámite de titulación en curso, y solo:

- en la fase evaluación de perfil, cuando no tiene un perfil de proyecto en revisión ni aprobado: el primero, o el siguiente a un rechazo;
- en la fase pre-defensa, cuando no tiene una versión en revisión ni aprobada en esa fase: la primera, o la siguiente a una versión con observaciones del tribunal;
- en la fase defensa formal, mientras la defensa formal no se realizó y solo si la pre-defensa dejó observaciones.

En la fase requisitos, sin tutor, no presenta versiones.

### BR-003: Numeración de las versiones

Las versiones del proyecto de un trámite de titulación se numeran de uno en uno, sin reiniciar la numeración al cambiar de fase.

### BR-004: Quién revisa cada versión

En la fase evaluación de perfil revisa el perfil de proyecto el docente de taller de grado. En la fase pre-defensa la versión llega a la vez al tutor y a los tres miembros del tribunal; solo las observaciones de los miembros del tribunal dejan la versión con observaciones y frenan el siguiente paso; las del tutor son orientativas. En la fase defensa formal la versión no se revisa; la reciben el tutor y los miembros del tribunal.

### BR-005: Perfil de proyecto sin revisor

Un perfil de proyecto presentado mientras no hay docente de taller de grado queda en revisión, sin revisor, hasta la primera designación (UC-003 BR-004).

### BR-006: Versión de la defensa formal

En la fase defensa formal el estudiante presenta versiones solo si la pre-defensa dejó observaciones; entonces puede presentar las que necesite, sin límite, hasta la defensa formal, y la última presentada es la que se defiende. Si la pre-defensa no dejó observaciones, no presenta versiones en esa fase y se defiende la versión aprobada de la fase pre-defensa.

### BR-007: Título del proyecto de grado

El estudiante ingresa el título con cada perfil de proyecto. Desde que un perfil de proyecto es aprobado (UC-005), el título del proyecto de grado queda fijo: las versiones de las fases pre-defensa y defensa formal llevan ese título, y el estudiante no lo cambia.
