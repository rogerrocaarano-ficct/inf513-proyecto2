# ADR-002: React se comunica con Laravel solo por Inertia, sin API JSON

**Status:** Accepted
**Date:** 2026-11-25
**Requirements:** C-002, C-009

## Context

Los lineamientos técnicos (sección 3.3) piden un backend con una "API RESTful" que React consume mediante un cliente
HTTP, y su diagrama de arquitectura (sección 2) admite "Inertia o Axios". C-002 fija React 19 comunicado con el
servidor mediante Inertia 3. C-009 exige separar la presentación de la lógica de negocio.

## Decision

- Las páginas React llegan a Laravel solo mediante visitas y formularios de Inertia, por rutas de `routes/web.php`
  usadas con las funciones generadas por Wayfinder.
- No hay `routes/api.php`, ni API Resources, ni endpoints JSON para otros clientes.
- Los controladores Inertia forman la capa de presentación del lado del servidor y delegan la lógica de negocio en
  las Actions (ADR-001).

## Consequences

- Hay un solo mecanismo de autenticación (la sesión de Fortify), un solo camino de validación y ningún contrato de
  API que versionar.
- Ningún otro cliente (una app móvil, otro sistema) puede usar el sistema; si aparece uno, un ADR nuevo reemplaza a
  este.
- **Alternatives considered:** una API REST JSON aparte, consumida por una SPA de React con un cliente HTTP,
  descartada porque exige dos contratos y manejo de tokens, y porque C-002 fija Inertia.
