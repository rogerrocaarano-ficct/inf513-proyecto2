# ADR-004: El proyecto no tiene pruebas automatizadas de navegador (E2E)

**Status:** Accepted
**Date:** 2026-11-25
**Requirements:** C-015

## Context

C-015 fija que un equipo de tres desarrolladores publica el sistema a más tardar el 30 de noviembre de 2026. Las
pruebas de navegador exigen una herramienta más, un servidor y un navegador en ejecución durante las pruebas, y su
mantenimiento cada vez que cambia una pantalla. Las pruebas Pest de feature de la capa UI ya recorren el controlador
Inertia, la Action y la base de datos real en proceso (ADR-001, ADR-002, ADR-003).

## Decision

- El proyecto no tiene capa E2E: `docs/architecture/stack.md` la marca con `–`.
- Cada caso de uso se prueba en su capa de integración con pruebas Pest de feature, que verifican la página Inertia
  devuelta y sus props, sin abrir un navegador.
- No se agregan dependencias de pruebas de navegador (Pest Browser, Playwright, Dusk).

## Consequences

- El tiempo del equipo se dedica a construir y probar los casos de uso, y la suite corre sin navegador.
- El código React (componentes, validación en el cliente que pide NFR-013, temas, contraste y escalado de NFR-014 y
  NFR-015, ancho de 360 píxeles de NFR-010, navegadores de C-003) no tiene pruebas automatizadas; se verifica a mano
  en el protocolo de aceptación.
- Si C-015 cambia, este ADR se revisa: se reemplaza por uno que agregue la capa E2E con `/stack`.
- **Alternatives considered:** Pest Browser solo para los recorridos TC-*, y Pest Browser para cada caso de uso y
  cada recorrido, descartadas ambas por el plazo de C-015.
