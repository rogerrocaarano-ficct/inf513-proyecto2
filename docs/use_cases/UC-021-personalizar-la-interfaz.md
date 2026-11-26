# Use Case: Personalizar la interfaz

## Overview

**Use Case ID:** UC-021  
**Use Case Name:** Personalizar la interfaz  
**Primary Actor:** Usuario  
**Goal:** Elegir el tema, el modo día o noche, el tamaño de la fuente y el alto contraste de la interfaz, para que el sistema se adapte a su preferencia y sea cómodo de leer  
**Trigger:** El usuario solicita cambiar la apariencia de la interfaz  
**Status:** Draft  

**Requirements:** [FR-031, FR-032, FR-033, FR-034, NFR-009, NFR-010, NFR-014, NFR-015, C-011](../requirements.md)  
**Layers:** UI

## Preconditions

- El usuario inició sesión.

## Main Success Scenario

1. El usuario solicita personalizar la interfaz.
2. El sistema muestra sus preferencias actuales: el tema, el modo día o noche, el tamaño de la fuente y el alto contraste.
3. El usuario elige el tema Niños, Jóvenes o Adultos, o restablecer la apariencia predeterminada.
4. El sistema aplica el tema a la página de inmediato.
5. El usuario elige el modo día, noche o el del sistema operativo.
6. El sistema aplica el modo de inmediato.
7. El usuario aumenta o reduce el tamaño de la fuente.
8. El sistema aplica el nuevo tamaño de la fuente de inmediato, entre el 75 % y el 200 % del tamaño normal.
9. El usuario activa o desactiva el alto contraste.
10. El sistema aplica el alto contraste de inmediato y guarda las preferencias del usuario.

## Alternative Flows

### A1: Límite del tamaño de la fuente

**Trigger:** El usuario intenta reducir la fuente por debajo del 75 % o aumentarla por encima del 200 % (step 7)  
**Flow:**

1. El sistema mantiene el tamaño en el límite e indica que es el mínimo o el máximo.
2. Use case continues at step 9.

### A2: Cambiar solo una preferencia

**Trigger:** El usuario cambia solo el tema, el modo, el tamaño de la fuente o el alto contraste, sin cambiar las demás preferencias (step 3, step 5, step 7 o step 9)  
**Flow:**

1. El usuario cambia la preferencia que quiere.
2. El sistema la aplica de inmediato y guarda las preferencias del usuario.
3. Use case ends.

### A3: Restablecer las preferencias

**Trigger:** El usuario elige restablecer la apariencia predeterminada (step 3)  
**Flow:**

1. El sistema vuelve a las preferencias predeterminadas (BR-004), las aplica de inmediato y las guarda.
2. Use case ends.

## Postconditions

### Success Postconditions

- Las preferencias del usuario están guardadas con su tema, su modo, su tamaño de fuente y su alto contraste.
- Todas las páginas que el usuario abre, en cualquier sesión y equipo, usan sus preferencias.

### Failure Postconditions

- Las preferencias de otros usuarios no cambian, y un tamaño de fuente fuera del 75 % al 200 % nunca se guarda.

## Business Rules

### BR-001: Preferencias por usuario

Cada usuario tiene sus propias preferencias, guardadas con su cuenta y aplicadas en todas sus sesiones.

### BR-002: Modo del sistema operativo

Con el modo del sistema operativo, la interfaz sigue el modo claro u oscuro que el sistema operativo del usuario indica, también si cambia durante la sesión (FR-032).

### BR-003: Accesibilidad

Todos los temas y modos cumplen la relación de contraste de NFR-014; el alto contraste, la de 7:1. Con la fuente al 200 % la interfaz se usa sin pérdida de contenido ni de funciones (NFR-015) y sin desplazamiento horizontal en un ancho de 360 píxeles (NFR-010).

### BR-004: Preferencias predeterminadas

Un usuario nuevo, o uno que restablece la apariencia, tiene el tema Adultos, el modo del sistema operativo, la fuente al 100 % y el alto contraste desactivado.

### BR-005: Tamaño de la fuente

El tamaño de la fuente cambia en pasos de 25 %, entre el 75 % y el 200 % del tamaño normal.
