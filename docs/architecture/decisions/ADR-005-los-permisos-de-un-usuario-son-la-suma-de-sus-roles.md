# ADR-005: Los permisos de un usuario son la suma de los de sus roles

**Status:** Accepted
**Date:** 2026-11-25
**Requirements:** NFR-005, C-015

## Context

El director de carrera es también docente y puede ser tutor (UC-018 BR-002), así que su cuenta necesita los permisos
de los dos roles. Los lineamientos técnicos (§3.2) piden un menú dinámico según los permisos del rol activo. Un
selector de rol activo obligaría al director a cambiar de rol para actuar como tutor y de vuelta para actuar como
director, y agregaría un flujo, un dato y sus pruebas. Con los roles del proyecto no hay vistas que choquen: como
director ya ve todos los trámites de titulación, y las reglas de cada caso de uso impiden los papeles incompatibles,
como que el director sea miembro de un tribunal (UC-006 BR-001).

## Decision

- Una cuenta puede tener uno o más de los roles fijos que define el sistema (FR-027); el rol director de carrera
  siempre va junto con el rol docente.
- Los permisos de un usuario son la unión de los permisos de todos sus roles. El menú (FR-029), los trámites de
  titulación que ve (UC-013 BR-001) y las acciones que se le ofrecen (UC-013 BR-004) salen de esa unión.
- No hay selector de rol activo: el conjunto de roles del usuario hace de rol activo para §3.2 de los lineamientos.
- Cada acción se sigue autorizando por permiso (NFR-005) y por las reglas de participación de su caso de uso
  (NFR-004).

## Consequences

- El director de carrera actúa como director y como tutor sin cambiar de rol; el modelo no guarda un rol activo.
- La bitácora registra al usuario que actúa, no el rol con que lo hizo.
- Si se agrega una combinación de roles cuyas vistas o acciones choquen, este ADR se revisa y se reemplaza por uno
  con selector de rol activo.
- **Alternatives considered:** un solo rol por usuario, con los permisos de docente dentro del rol director de
  carrera, descartada porque duplica permisos entre roles; varios roles con un selector de rol activo que cambia el
  propio usuario, descartada por la fricción para el director y el trabajo extra frente al plazo de C-015.
