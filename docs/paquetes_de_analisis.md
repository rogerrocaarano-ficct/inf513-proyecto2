# Paquetes de análisis

**Fecha:** 2026-11-26

## Propósito

Este documento agrupa los veintiún casos de uso de `docs/use_cases/` en paquetes de análisis. Cada paquete reúne los
casos de uso que tratan la misma parte del dominio, comparten actores y trabajan sobre las mismas entidades. Los
paquetes sirven para organizar el modelo de análisis y para repartir la construcción.

Cada caso de uso pertenece a un solo paquete. Los requisitos funcionales de cada paquete salen de la línea
`**Requirements:**` de sus casos de uso.

## Resumen

| # | Paquete                                     | Casos de uso                                       | Actores principales                                                                 |
|---|---------------------------------------------|----------------------------------------------------|-------------------------------------------------------------------------------------|
| 1 | Gestión y revisión de proyectos de grado    | UC-001, UC-004, UC-005, UC-007, UC-008, UC-012     | Asistente administrativo, Estudiante, Revisor, Tutor, Miembro del tribunal          |
| 2 | Gestión de plantel docente                  | UC-002, UC-003, UC-006                             | Director de carrera                                                                 |
| 3 | Defensa y calificación                      | UC-009, UC-010, UC-011                             | Director de carrera, Miembro del tribunal                                           |
| 4 | Reportes y notificaciones                   | UC-013, UC-014, UC-015, UC-016, UC-017             | Estudiante, Docente, Asistente administrativo, Director de carrera                  |
| 5 | Administración del sistema                  | UC-018, UC-019, UC-020, UC-021                     | Administrador del sistema, Usuario                                                  |

## Paquetes

### 1. Gestión y revisión de proyectos de grado

Cubre el trámite de titulación de punta a punta, salvo la defensa. Incluye el registro con la documentación de
requisitos, las versiones del proyecto con sus observaciones (desde el perfil de proyecto hasta la versión que llega a
la defensa formal) y el archivo final.

| ID     | Caso de uso                                  | Actor principal          |
|--------|----------------------------------------------|--------------------------|
| UC-001 | Iniciar trámite de titulación                | Asistente administrativo |
| UC-004 | Presentar versión del proyecto               | Estudiante               |
| UC-005 | Evaluar perfil de proyecto                   | Revisor                  |
| UC-007 | Observar versión del proyecto como tutor     | Tutor                    |
| UC-008 | Revisar versión del proyecto como tribunal   | Miembro del tribunal     |
| UC-012 | Archivar trámite de titulación               | Asistente administrativo |

**Requisitos funcionales:** FR-001, FR-002, FR-005, FR-007, FR-009, FR-010, FR-011, FR-012, FR-014, FR-020, FR-041.

**Entidades principales:** trámite de titulación, documentación de requisitos, perfil de proyecto, versión del
proyecto, observación.

### 2. Gestión de plantel docente

Asigna a los docentes que atienden un trámite de titulación y define el rol de cada uno: tutor, docente de taller de
grado y tribunal evaluador.

| ID     | Caso de uso                              | Actor principal     |
|--------|------------------------------------------|---------------------|
| UC-002 | Asignar tutor                            | Director de carrera |
| UC-003 | Designar docente de taller de grado      | Director de carrera |
| UC-006 | Asignar tribunal evaluador               | Director de carrera |

**Requisitos funcionales:** FR-004, FR-006, FR-008, FR-018.

**Entidades principales:** docente, tutor, tribunal evaluador, trámite de titulación.

### 3. Defensa y calificación

Programa la pre-defensa y la defensa formal y registra sus observaciones. También registra la calificación con la que
el trámite de titulación concluye aprobado o reprobado.

| ID     | Caso de uso                                 | Actor principal      |
|--------|---------------------------------------------|----------------------|
| UC-009 | Programar pre-defensa y defensa formal      | Director de carrera  |
| UC-010 | Registrar observaciones de pre-defensa      | Miembro del tribunal |
| UC-011 | Registrar calificación                      | Miembro del tribunal |

**Requisitos funcionales:** FR-015, FR-016, FR-017, FR-018, FR-019, FR-042.

**Entidades principales:** pre-defensa, defensa formal, observación, calificación.

### 4. Reportes y notificaciones

Reúne las consultas de solo lectura sobre los trámites de titulación (detalle, trámites en curso, búsqueda y
estadísticas) y las notificaciones que el sistema muestra a cada usuario.

| ID     | Caso de uso                                 | Actor principal                                                        |
|--------|---------------------------------------------|------------------------------------------------------------------------|
| UC-013 | Consultar trámite de titulación             | Estudiante, Docente, Asistente administrativo, Director de carrera     |
| UC-014 | Consultar trámites en curso                 | Asistente administrativo                                               |
| UC-015 | Buscar trámite de titulación                | Estudiante, Docente, Asistente administrativo, Director de carrera     |
| UC-016 | Consultar notificaciones                    | Estudiante, Docente                                                    |
| UC-017 | Consultar estadísticas de trámites          | Director de carrera                                                    |

**Requisitos funcionales:** FR-013, FR-021, FR-022, FR-023, FR-024, FR-036, FR-038, FR-040.

**Entidades principales:** trámite de titulación, notificación.

### 5. Administración del sistema

Gestiona las cuentas de usuario y sus roles, la bitácora y las estadísticas de acceso, y las preferencias de interfaz
de cada usuario.

| ID     | Caso de uso                         | Actor principal           |
|--------|-------------------------------------|---------------------------|
| UC-018 | Gestionar cuentas de usuario        | Administrador del sistema |
| UC-019 | Consultar bitácora                  | Administrador del sistema |
| UC-020 | Consultar estadísticas de acceso    | Administrador del sistema |
| UC-021 | Personalizar la interfaz            | Usuario                   |

**Requisitos funcionales:** FR-003, FR-027, FR-029, FR-030, FR-031, FR-032, FR-033, FR-034, FR-035, FR-037.

**Entidades principales:** usuario, rol, bitácora.

## Dependencias entre paquetes

- **Gestión y revisión de proyectos de grado** es el núcleo. Abre el trámite de titulación sobre el que trabajan los
  demás paquetes y lo cierra con el archivo.
- **Gestión de plantel docente** asigna docentes a un trámite de titulación ya iniciado. Solo los docentes asignados
  aquí revisan versiones del proyecto (paquete 1) y participan en la defensa (paquete 3).
- **Defensa y calificación** depende de un tribunal evaluador asignado (paquete 2) y de una versión del proyecto lista
  para la pre-defensa o la defensa formal (paquete 1).
- **Reportes y notificaciones** solo lee lo que producen los paquetes 1, 2 y 3. Los casos de uso de esos paquetes
  generan las notificaciones (FR-021, FR-022) y UC-016 las muestra.
- **Administración del sistema** es la base de todos: las cuentas de usuario y sus roles deciden quién actúa en cada
  paquete (ADR-005: los permisos de un usuario son la suma de sus roles).

## Decisiones de agrupación

- **UC-001 y UC-012 van en el paquete 1**, junto con la revisión de versiones, porque abren y cierran el mismo trámite
  de titulación que esas versiones hacen avanzar.
- **UC-017 va en Reportes y notificaciones y UC-020 en Administración del sistema.** UC-017 mide trámites de
  titulación para el director de carrera. UC-020 mide el uso del sistema para el administrador.
- **UC-021 va en Administración del sistema** porque no pertenece a ningún paquete del dominio académico.
