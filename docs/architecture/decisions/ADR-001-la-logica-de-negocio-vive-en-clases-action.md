# ADR-001: La lógica de negocio vive en clases Action

**Status:** Accepted
**Date:** 2026-11-25
**Requirements:** C-009, C-001

## Context

C-009 exige dividir el sistema en capa de presentación, capa de lógica de negocio y capa de persistencia. Laravel 13
(C-001) no impone una capa de lógica de negocio: por defecto, las reglas terminan en los controladores o en los
modelos Eloquent. El proyecto ya usa clases Action para el registro y el perfil de usuario (`app/Actions/Fortify`).

## Decision

- Cada operación de negocio de un caso de uso es una clase Action de un solo propósito en `app/Actions/<Área>/`.
- La Action contiene las reglas de negocio y la transacción de la operación.
- El controlador solo valida con un Form Request, autoriza con una Policy, llama a la Action y devuelve la página
  Inertia.
- Los modelos Eloquent contienen solo relaciones, casts y scopes.
- Los comandos programados y los jobs llaman a las mismas Actions que los controladores.

## Consequences

- Las reglas de negocio se prueban llamando a la Action, sin pasar por HTTP, y la misma regla se reutiliza desde la
  pantalla, un comando o un job.
- Hay muchas clases pequeñas, una por operación.
- **Alternatives considered:** servicios por agregado en `app/Services`, descartados porque mezclan varias
  operaciones en una clase grande; lógica en los controladores o en los modelos, descartada porque incumple C-009.
