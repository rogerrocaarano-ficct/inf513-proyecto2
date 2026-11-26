# Use Case: Consultar estadísticas de acceso

## Overview

**Use Case ID:** UC-020  
**Use Case Name:** Consultar estadísticas de acceso  
**Primary Actor:** Administrador del sistema  
**Goal:** Ver por día los inicios de sesión, los intentos fallidos y las páginas más visitadas, para vigilar el flujo de accesos al sistema  
**Trigger:** El administrador del sistema solicita ver el panel de estadísticas de acceso  
**Status:** Draft  

**Requirements:** [FR-035, FR-037, NFR-001, NFR-005, NFR-009, NFR-010, NFR-011, NFR-012, NFR-013](../requirements.md)  
**Layers:** UI

## Preconditions

- El administrador del sistema inició sesión y tiene el rol administrador del sistema.

## Main Success Scenario

1. El administrador del sistema solicita ver el panel de estadísticas de acceso.
2. El sistema muestra, para cada día de los últimos 30 días, la cantidad de inicios de sesión exitosos y de intentos fallidos.
3. El sistema muestra las 10 páginas más visitadas en ese periodo, con su cantidad de visitas, y la cantidad de visitas por día.
4. El administrador del sistema cambia el rango de fechas.
5. El sistema verifica en el servidor que la fecha inicial no es posterior a la final y que el rango no supera los 365 días consecutivos.
6. El sistema muestra las mismas cifras para el nuevo rango.

## Alternative Flows

### A1: Rango de fechas inválido

**Trigger:** La fecha inicial es posterior a la final, o el rango supera 365 días consecutivos (step 5)  
**Flow:**

1. El sistema informa el motivo con un mensaje en español y conserva el rango anterior.
2. El administrador del sistema corrige las fechas.
3. Use case continues at step 5.

### A2: Sin accesos en el rango

**Trigger:** No hay inicios de sesión ni visitas en el rango elegido (step 6)  
**Flow:**

1. El sistema muestra las cifras en cero e informa que no hubo accesos en ese rango.
2. Use case ends.

### A3: El administrador del sistema no cambia el rango

**Trigger:** El administrador del sistema solo consulta los últimos 30 días (step 4)  
**Flow:**

1. El administrador del sistema sale después de ver las cifras.
2. Use case ends.

## Postconditions

### Success Postconditions

- El administrador del sistema vio por día los inicios de sesión exitosos, los intentos fallidos y las visitas, y las páginas más visitadas del rango elegido.

### Failure Postconditions

_None — the use case changes no data._

## Business Rules

### BR-001: Fuente de las cifras

Las cifras se calculan con la bitácora: los inicios de sesión exitosos y fallidos (NFR-011) y las visitas a páginas (NFR-012), con los datos registrados al momento de la consulta.

### BR-002: Páginas más visitadas

Las páginas más visitadas son las 10 con más visitas en el rango elegido, ordenadas de más a menos visitas.

### BR-003: Contador de visitas

El contador de visitas que cada página muestra en su pie de página a todo usuario es el número total de visitas registradas de esa página, las mismas que cuenta este panel (FR-035).

### BR-004: Solo el administrador del sistema

Solo el administrador del sistema consulta las estadísticas de acceso (NFR-005).
