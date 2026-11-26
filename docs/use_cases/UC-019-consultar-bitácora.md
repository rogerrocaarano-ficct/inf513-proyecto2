# Use Case: Consultar bitácora

## Overview

**Use Case ID:** UC-019  
**Use Case Name:** Consultar bitácora  
**Primary Actor:** Administrador del sistema  
**Goal:** Consultar los inicios de sesión exitosos y fallidos con su dirección IP, filtrados por fecha y usuario, para detectar accesos indebidos  
**Trigger:** El administrador del sistema solicita consultar la bitácora de inicios de sesión, por ejemplo ante una cuenta bloqueada o un acceso sospechoso  
**Status:** Reviewed  

**Requirements:** [FR-030, NFR-001, NFR-005, NFR-006, NFR-009, NFR-010, NFR-011, NFR-013](../requirements.md)  
**Layers:** UI

## Preconditions

- El administrador del sistema inició sesión y tiene el rol administrador del sistema.

## Main Success Scenario

1. El administrador del sistema solicita consultar la bitácora de inicios de sesión.
2. El sistema muestra los intentos de inicio de sesión de los últimos 7 días, del más reciente al más antiguo, cada uno con la fecha y hora, el correo electrónico con que se intentó, el usuario al que corresponde si existe, si fue exitoso o fallido, la dirección IP y el navegador.
3. El administrador del sistema filtra por rango de fechas, por usuario o correo electrónico, o por resultado exitoso o fallido.
4. El sistema verifica que la fecha inicial no es posterior a la final.
5. El sistema muestra los intentos que cumplen el filtro, con la cantidad de exitosos y de fallidos.

## Alternative Flows

### A1: Rango de fechas inválido

**Trigger:** La fecha inicial es posterior a la fecha final (step 4)  
**Flow:**

1. El sistema informa, con un mensaje en español, que la fecha inicial no puede ser posterior a la final, y conserva el filtro.
2. El administrador del sistema corrige las fechas.
3. Use case continues at step 4.

### A2: Sin resultados

**Trigger:** Ningún intento de inicio de sesión cumple el filtro (step 5)  
**Flow:**

1. El sistema informa que no hay intentos de inicio de sesión para ese filtro.
2. Use case ends.

### A3: Sin filtro

**Trigger:** El administrador del sistema no aplica un filtro (step 3)  
**Flow:**

1. El administrador del sistema consulta la lista de los últimos 7 días y sale.
2. Use case ends.

## Postconditions

### Success Postconditions

- El administrador del sistema vio los intentos de inicio de sesión que cumplen el filtro, con su resultado y su dirección IP.

### Failure Postconditions

_None — the use case changes no data._

## Business Rules

### BR-001: Contenido de la bitácora de inicios de sesión

La bitácora muestra el 100 % de los intentos de inicio de sesión, exitosos y fallidos, con fecha y hora, usuario y dirección IP (NFR-011), también los intentos con un correo electrónico que no corresponde a ningún usuario.

### BR-002: Solo inicios de sesión

Esta consulta muestra solo los inicios de sesión. Las visitas a páginas no se consultan aquí; se ven agregadas en las estadísticas de acceso (UC-020).

### BR-003: Bitácora inalterable

La bitácora no se cambia ni se borra desde el sistema; el administrador del sistema solo la consulta.

### BR-004: Solo el administrador del sistema

Solo el administrador del sistema consulta la bitácora (NFR-005).
