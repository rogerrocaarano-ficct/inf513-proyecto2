# Use Case: Consultar estadísticas de trámites

## Overview

**Use Case ID:** UC-017  
**Use Case Name:** Consultar estadísticas de trámites  
**Primary Actor:** Director de carrera  
**Goal:** Ver un panel con las cifras de los trámites de titulación de la carrera, para evaluar su avance  
**Trigger:** El director de carrera solicita ver el panel de estadísticas de trámites  
**Status:** Draft  

**Requirements:** [FR-036, NFR-001, NFR-005, NFR-009, NFR-010](../requirements.md)  
**Layers:** UI

## Preconditions

- El director de carrera inició sesión y tiene el rol director de carrera.

## Main Success Scenario

1. El director de carrera solicita ver el panel de estadísticas de trámites.
2. El sistema muestra la cantidad de trámites de titulación en curso por fase.
3. El sistema muestra las pre-defensas y defensas formales programadas sin realizar, con su cantidad y la próxima fecha de cada tipo.
4. El sistema muestra la cantidad de trámites de titulación concluidos aprobados, concluidos reprobados y abandonados, y la calificación promedio de las defensas formales realizadas.
5. El director de carrera elige una fase.
6. El sistema muestra los trámites de titulación en curso de esa fase, con el estudiante, el título del proyecto de grado y la fecha en que entraron en ella.

## Alternative Flows

### A1: El director de carrera solo consulta las cifras

**Trigger:** El director de carrera no elige una fase (step 5)  
**Flow:**

1. El director de carrera sale después de ver las cifras.
2. Use case ends.

### A2: Sin defensas formales realizadas

**Trigger:** Aún no hay defensas formales realizadas con calificación (step 4)  
**Flow:**

1. El sistema muestra la calificación promedio como no disponible, en lugar de un valor.
2. Use case continues at step 5.

## Postconditions

### Success Postconditions

- El director de carrera vio las cifras de los trámites de titulación, calculadas con los datos registrados al momento de la consulta.

### Failure Postconditions

_None — the use case changes no data._

## Business Rules

### BR-001: Métricas del panel

El panel muestra solo estas métricas (FR-036): los trámites de titulación en curso por fase, las pre-defensas y defensas formales programadas sin realizar, los trámites concluidos aprobados, concluidos reprobados y abandonados, y la calificación promedio.

### BR-002: Calificación promedio

La calificación promedio es el promedio de las calificaciones conjuntas de todas las defensas formales realizadas con el estudiante presente (UC-011 BR-003), redondeado a un decimal con las mitades hacia arriba. Las defensas formales con inasistencia no cuentan.

### BR-003: Datos al momento

Las cifras se calculan con los datos registrados al momento de la consulta, de todos los trámites de titulación de la carrera, sin filtro por periodo.
