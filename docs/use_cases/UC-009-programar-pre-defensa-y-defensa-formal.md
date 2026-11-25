# Use Case: Programar pre-defensa y defensa formal

## Overview

**Use Case ID:** UC-009  
**Use Case Name:** Programar pre-defensa y defensa formal  
**Primary Actor:** Director de carrera  
**Goal:** Fijar la fecha, la hora y el ambiente de la pre-defensa o la defensa formal de un proyecto de grado sin superponer al tribunal evaluador, para que el estudiante, su tutor y su tribunal la conozcan  
**Trigger:** El director de carrera solicita programar o reprogramar una pre-defensa o defensa formal  
**Status:** Draft  

**Requirements:** [FR-015, FR-017, FR-018, FR-021, NFR-005, NFR-009, NFR-013](../requirements.md)  
**Layers:** UI

## Preconditions

- El director de carrera inició sesión con el rol director de carrera.

## Main Success Scenario

1. El director de carrera solicita programar una pre-defensa o defensa formal.
2. El sistema muestra el calendario de las pre-defensas y defensas formales programadas sin realizar, con su fecha, hora, ambiente, estudiante y tribunal evaluador, y la lista de los trámites de titulación que esperan programación (BR-001, BR-002).
3. El director de carrera elige un trámite de titulación de la lista.
4. El sistema muestra si corresponde programar su pre-defensa o su defensa formal, con su tutor y los miembros vigentes de su tribunal evaluador.
5. El director de carrera ingresa la fecha, la hora y, si ya lo conoce, el ambiente.
6. El director de carrera confirma la programación.
7. El sistema verifica que la fecha y hora son futuras y que en esas dos horas ningún miembro del tribunal, ni el tutor, ni el ambiente tienen otra pre-defensa o defensa formal programada (BR-003).
8. El sistema registra la pre-defensa o defensa formal programada, o actualiza la que se reprograma.
9. El sistema notifica al estudiante, a su tutor y a los miembros de su tribunal evaluador la fecha, la hora y el ambiente programados, o su cambio.
10. El sistema muestra el calendario actualizado.

## Alternative Flows

### A1: Choque de horario

**Trigger:** Un miembro del tribunal, el tutor o el ambiente tiene otra pre-defensa o defensa formal que se cruza con el horario elegido (step 7)  
**Flow:**

1. El sistema señala quién o qué ambiente tiene el choque y con qué pre-defensa o defensa formal, y conserva lo ingresado.
2. El director de carrera elige otra fecha, hora o ambiente.
3. Use case continues at step 6.

### A2: Fecha pasada

**Trigger:** La fecha y hora elegidas no son futuras (step 7)  
**Flow:**

1. El sistema informa, con un mensaje en español, que la fecha y hora deben ser futuras, y conserva lo ingresado.
2. El director de carrera corrige la fecha o la hora.
3. Use case continues at step 6.

### A3: Reprogramación

**Trigger:** El director de carrera elige en el calendario una pre-defensa o defensa formal programada que aún no se realizó (step 3)  
**Flow:**

1. El sistema muestra su fecha, hora y ambiente actuales, con el tutor y los miembros vigentes del tribunal evaluador.
2. El director de carrera ingresa la nueva fecha, hora o ambiente.
3. Use case continues at step 6.

### A4: Nada que programar

**Trigger:** No hay trámites de titulación que esperen programación y el director de carrera no elige una pre-defensa o defensa formal para reprogramar (step 3)  
**Flow:**

1. El director de carrera consulta el calendario y sale.
2. Use case ends.

### A5: El director de carrera desiste

**Trigger:** El director de carrera no confirma la programación (step 6)  
**Flow:**

1. El director de carrera cancela la operación.
2. Use case ends.

## Postconditions

### Success Postconditions

- El trámite de titulación tiene su pre-defensa o defensa formal programada con la fecha, la hora y, si se conoce, el ambiente, sin realizar.
- Ningún miembro del tribunal, tutor ni ambiente tiene dos pre-defensas o defensas formales que se crucen.
- El estudiante, su tutor y los miembros vigentes de su tribunal evaluador tienen una notificación de la fecha programada o de su cambio.

### Failure Postconditions

- Las pre-defensas y defensas formales programadas no cambian.

## Business Rules

### BR-001: Cuándo se programa la pre-defensa

La pre-defensa se programa para un trámite de titulación en curso cuya versión de la fase pre-defensa aprobaron los tres miembros vigentes del tribunal (UC-008 BR-003), y que no tiene otra pre-defensa sin realizar.

### BR-002: Cuándo se programa la defensa formal

La defensa formal se programa para un trámite de titulación en curso en la fase defensa formal, es decir, con su pre-defensa realizada (UC-010), y que no tiene otra defensa formal sin realizar. No espera a que el estudiante presente una versión corregida: el estudiante la presenta antes de la fecha (UC-004 BR-006).

### BR-003: Duración y choques

Cada pre-defensa o defensa formal ocupa dos horas desde su hora de inicio. Dos pre-defensas o defensas formales cuyos horarios se cruzan no pueden tener un mismo miembro del tribunal vigente, un mismo tutor ni un mismo ambiente.

### BR-004: Fecha futura

Una pre-defensa o defensa formal se programa o reprograma solo para una fecha y hora futuras.

### BR-005: Reprogramación

Solo una pre-defensa o defensa formal que aún no se realizó se reprograma; al hacerlo se notifica el cambio al estudiante, al tutor y a los miembros vigentes del tribunal evaluador.

### BR-006: Calendario de defensas

El calendario muestra todas las pre-defensas y defensas formales programadas sin realizar, de todos los trámites de titulación, para que el director de carrera asigne fechas sin superponer al tribunal evaluador (FR-018).
