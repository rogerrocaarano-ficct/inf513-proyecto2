# ADR-003: Las pruebas de integración corren sobre PostgreSQL

**Status:** Accepted
**Date:** 2026-11-25
**Requirements:** C-008

## Context

C-008 fija PostgreSQL como base de datos, y los lineamientos técnicos (sección 2) prevén triggers y contadores en
ella. `phpunit.xml` corre hoy las pruebas sobre SQLite en memoria, que difiere de PostgreSQL en tipos, restricciones
y triggers.

## Decision

- Las pruebas que tocan la base de datos corren sobre una base PostgreSQL local dedicada a pruebas, separada de la
  base de desarrollo.
- `phpunit.xml` apunta a esa base, y las pruebas usan `RefreshDatabase`.

## Consequences

- Las pruebas ejercitan los tipos, las restricciones y los triggers reales de PostgreSQL.
- PostgreSQL tiene que estar disponible donde corran las pruebas, y las pruebas son más lentas que en memoria.
- **Alternatives considered:** SQLite en memoria, descartada porque, aunque es más rápida, no prueba lo específico
  de PostgreSQL.
