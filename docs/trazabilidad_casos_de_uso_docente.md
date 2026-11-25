# Trazabilidad entre los casos de uso del docente y los del proyecto

**Fecha:** 2026-11-25

## Propósito

El docente asignó al proyecto ocho casos de uso generales, resultado de su primer análisis. El equipo los refinó en
veintiún casos de uso atómicos, uno por objetivo de un actor, especificados en `docs/use_cases/`. Este documento
muestra qué casos de uso del proyecto realizan cada caso de uso del docente, y al revés.

Para no confundir los identificadores, los casos de uso del docente llevan aquí el prefijo `UCD-` (por ejemplo,
el UC-001 del docente es `UCD-001`). Los identificadores `UC-XXX` son siempre los del proyecto.

**Tipo de relación:**

- **Total:** el caso de uso del proyecto realiza una parte central del caso de uso del docente.
- **Parcial:** el caso de uso del proyecto contribuye a una parte secundaria, o el caso de uso del docente solo cubre
  una parte de él.

## Casos de uso del docente

| ID      | Título                                                       |
|---------|--------------------------------------------------------------|
| UCD-001 | Gestión de usuarios                                          |
| UCD-002 | Habilitación académica de solicitudes                        |
| UCD-003 | Asignación de tutor y designación de tribunal                |
| UCD-004 | Evaluación del perfil de proyecto                            |
| UCD-005 | Revisión y versionado del documento del proyecto             |
| UCD-006 | Programación y registro de evaluaciones                      |
| UCD-007 | Monitoreo, trazabilidad, dashboard y calendario académico    |
| UCD-008 | Reportes y estadísticas                                      |

## Del docente al proyecto

### UCD-001 Gestión de usuarios

| Caso de uso del proyecto                                                                  | Relación | Nota                                                                                                     |
|-------------------------------------------------------------------------------------------|----------|----------------------------------------------------------------------------------------------------------|
| [UC-018 Gestionar cuentas de usuario](use_cases/UC-018-gestionar-cuentas-de-usuario.md)   | Total    | Registro, cambio, desactivación y reactivación de las cuentas del personal de la carrera, con su rol.    |
| [UC-001 Iniciar trámite de titulación](use_cases/UC-001-iniciar-trámite-de-titulación.md) | Parcial  | Las cuentas de los estudiantes no las crea el administrador: se crean al iniciar su trámite de titulación. |
| [UC-019 Consultar bitácora](use_cases/UC-019-consultar-bitácora.md)                       | Parcial  | Supervisión de los inicios de sesión de las cuentas, a cargo del administrador del sistema.              |
| [UC-021 Personalizar la interfaz](use_cases/UC-021-personalizar-la-interfaz.md)           | Parcial  | Preferencias de tema, modo día o noche, fuente y contraste que cada usuario guarda en su cuenta.         |

### UCD-002 Habilitación académica de solicitudes

| Caso de uso del proyecto                                                                  | Relación | Nota                                                                                                                          |
|-------------------------------------------------------------------------------------------|----------|-------------------------------------------------------------------------------------------------------------------------------|
| [UC-001 Iniciar trámite de titulación](use_cases/UC-001-iniciar-trámite-de-titulación.md) | Total    | El asistente administrativo registra la solicitud con la documentación de requisitos, verificada al confirmar el inicio.      |
| [UC-005 Evaluar perfil de proyecto](use_cases/UC-005-evaluar-perfil-de-proyecto.md)       | Parcial  | La viabilidad académica del proyecto de grado se decide al aprobar o rechazar el perfil de proyecto, no al iniciar el trámite. |

### UCD-003 Asignación de tutor y designación de tribunal

| Caso de uso del proyecto                                                                              | Relación | Nota                                                                                       |
|-------------------------------------------------------------------------------------------------------|----------|--------------------------------------------------------------------------------------------|
| [UC-002 Asignar tutor](use_cases/UC-002-asignar-tutor.md)                                             | Total    | Asignación o cambio del tutor por el director de carrera.                                  |
| [UC-006 Asignar tribunal evaluador](use_cases/UC-006-asignar-tribunal-evaluador.md)                   | Total    | Asignación de los tres miembros del tribunal evaluador y reemplazo de uno de ellos.       |
| [UC-003 Designar docente de taller de grado](use_cases/UC-003-designar-docente-de-taller-de-grado.md) | Parcial  | Designación del revisor de los perfiles de proyecto; el docente no la distinguió aparte.  |
| [UC-001 Iniciar trámite de titulación](use_cases/UC-001-iniciar-trámite-de-titulación.md)             | Parcial  | El tutor propuesto en la carta de solicitud puede quedar asignado al iniciar el trámite.  |

### UCD-004 Evaluación del perfil de proyecto

| Caso de uso del proyecto                                                                       | Relación | Nota                                                                             |
|------------------------------------------------------------------------------------------------|----------|----------------------------------------------------------------------------------|
| [UC-005 Evaluar perfil de proyecto](use_cases/UC-005-evaluar-perfil-de-proyecto.md)            | Total    | El revisor aprueba o rechaza el perfil de proyecto con sus observaciones.        |
| [UC-004 Presentar versión del proyecto](use_cases/UC-004-presentar-versión-del-proyecto.md)    | Parcial  | El estudiante sube el perfil de proyecto, también después de un rechazo.         |

### UCD-005 Revisión y versionado del documento del proyecto

| Caso de uso del proyecto                                                                                              | Relación | Nota                                                                                          |
|-----------------------------------------------------------------------------------------------------------------------|----------|-----------------------------------------------------------------------------------------------|
| [UC-004 Presentar versión del proyecto](use_cases/UC-004-presentar-versión-del-proyecto.md)                           | Total    | Cada entrega del estudiante queda como una versión del proyecto nueva; ninguna se borra.     |
| [UC-007 Observar versión del proyecto como tutor](use_cases/UC-007-observar-versión-del-proyecto-como-tutor.md)       | Total    | Observaciones del tutor sobre cada versión del proyecto.                                     |
| [UC-008 Revisar versión del proyecto como tribunal](use_cases/UC-008-revisar-versión-del-proyecto-como-tribunal.md)   | Total    | Observaciones o aprobación de cada miembro del tribunal, con el historial de versiones.      |
| [UC-013 Consultar trámite de titulación](use_cases/UC-013-consultar-trámite-de-titulación.md)                         | Parcial  | El estudiante consulta y descarga sus versiones del proyecto con sus observaciones.          |

### UCD-006 Programación y registro de evaluaciones

| Caso de uso del proyecto                                                                                   | Relación | Nota                                                                                                      |
|------------------------------------------------------------------------------------------------------------|----------|-----------------------------------------------------------------------------------------------------------|
| [UC-009 Programar pre-defensa y defensa formal](use_cases/UC-009-programar-pre-defensa-y-defensa-formal.md) | Total    | El director de carrera asigna fecha, hora y ambiente de la pre-defensa y de la defensa formal.           |
| [UC-010 Registrar observaciones de pre-defensa](use_cases/UC-010-registrar-observaciones-de-pre-defensa.md) | Total    | Registro del resultado de la pre-defensa, o de la inasistencia del estudiante.                           |
| [UC-011 Registrar calificación](use_cases/UC-011-registrar-calificación.md)                                 | Total    | Calificación de cada miembro del tribunal y cálculo de la calificación conjunta de la defensa formal.    |

### UCD-007 Monitoreo, trazabilidad, dashboard y calendario académico

| Caso de uso del proyecto                                                                                   | Relación | Nota                                                                                                     |
|------------------------------------------------------------------------------------------------------------|----------|----------------------------------------------------------------------------------------------------------|
| [UC-013 Consultar trámite de titulación](use_cases/UC-013-consultar-trámite-de-titulación.md)               | Total    | Trazabilidad del trámite: fase actual, fases concluidas, versiones y observaciones.                     |
| [UC-014 Consultar trámites en curso](use_cases/UC-014-consultar-trámites-en-curso.md)                       | Total    | Monitoreo de la fase actual de todos los trámites de titulación en curso.                               |
| [UC-016 Consultar notificaciones](use_cases/UC-016-consultar-notificaciones.md)                             | Total    | Aviso de cada cambio de los trámites de titulación en los que participa el usuario.                     |
| [UC-009 Programar pre-defensa y defensa formal](use_cases/UC-009-programar-pre-defensa-y-defensa-formal.md) | Total    | El calendario de pre-defensas y defensas formales es parte de este caso de uso (FR-018).                |
| [UC-017 Consultar estadísticas de trámites](use_cases/UC-017-consultar-estadísticas-de-trámites.md)         | Total    | El dashboard del director de carrera.                                                                    |
| [UC-015 Buscar trámite de titulación](use_cases/UC-015-buscar-trámite-de-titulación.md)                     | Parcial  | Búsqueda global para llegar a un trámite de titulación sin recorrer listas.                             |
| [UC-012 Archivar trámite de titulación](use_cases/UC-012-archivar-trámite-de-titulación.md)                 | Parcial  | Cierre del trámite concluido o abandonado, para que deje de figurar entre los pendientes.               |

### UCD-008 Reportes y estadísticas

| Caso de uso del proyecto                                                                            | Relación | Nota                                                                                                    |
|-----------------------------------------------------------------------------------------------------|----------|---------------------------------------------------------------------------------------------------------|
| [UC-017 Consultar estadísticas de trámites](use_cases/UC-017-consultar-estadísticas-de-trámites.md) | Total    | Trámites de titulación por fase, defensas formales programadas y calificación promedio.                 |
| [UC-020 Consultar estadísticas de acceso](use_cases/UC-020-consultar-estadísticas-de-acceso.md)     | Total    | Inicios de sesión, intentos fallidos y páginas más visitadas por día.                                   |
| [UC-019 Consultar bitácora](use_cases/UC-019-consultar-bitácora.md)                                 | Parcial  | Listado de inicios de sesión filtrado por fecha y usuario.                                              |

## Del proyecto al docente

| Caso de uso del proyecto                           | Casos de uso del docente                       |
|----------------------------------------------------|------------------------------------------------|
| UC-001 Iniciar trámite de titulación               | UCD-002 (Total), UCD-001, UCD-003 (Parcial)    |
| UC-002 Asignar tutor                               | UCD-003 (Total)                                |
| UC-003 Designar docente de taller de grado         | UCD-003 (Parcial)                              |
| UC-004 Presentar versión del proyecto              | UCD-005 (Total), UCD-004 (Parcial)             |
| UC-005 Evaluar perfil de proyecto                  | UCD-004 (Total), UCD-002 (Parcial)             |
| UC-006 Asignar tribunal evaluador                  | UCD-003 (Total)                                |
| UC-007 Observar versión del proyecto como tutor    | UCD-005 (Total)                                |
| UC-008 Revisar versión del proyecto como tribunal  | UCD-005 (Total)                                |
| UC-009 Programar pre-defensa y defensa formal      | UCD-006 (Total), UCD-007 (Total)               |
| UC-010 Registrar observaciones de pre-defensa      | UCD-006 (Total)                                |
| UC-011 Registrar calificación                      | UCD-006 (Total)                                |
| UC-012 Archivar trámite de titulación              | UCD-007 (Parcial)                              |
| UC-013 Consultar trámite de titulación             | UCD-007 (Total), UCD-005 (Parcial)             |
| UC-014 Consultar trámites en curso                 | UCD-007 (Total)                                |
| UC-015 Buscar trámite de titulación                | UCD-007 (Parcial)                              |
| UC-016 Consultar notificaciones                    | UCD-007 (Total)                                |
| UC-017 Consultar estadísticas de trámites          | UCD-007 (Total), UCD-008 (Total)               |
| UC-018 Gestionar cuentas de usuario                | UCD-001 (Total)                                |
| UC-019 Consultar bitácora                          | UCD-001, UCD-008 (Parcial)                     |
| UC-020 Consultar estadísticas de acceso            | UCD-008 (Total)                                |
| UC-021 Personalizar la interfaz                    | UCD-001 (Parcial)                              |

## Matriz

● relación total, ○ relación parcial.

| Caso de uso del proyecto | UCD-001 | UCD-002 | UCD-003 | UCD-004 | UCD-005 | UCD-006 | UCD-007 | UCD-008 |
|--------------------------|:-------:|:-------:|:-------:|:-------:|:-------:|:-------:|:-------:|:-------:|
| UC-001                   |    ○    |    ●    |    ○    |         |         |         |         |         |
| UC-002                   |         |         |    ●    |         |         |         |         |         |
| UC-003                   |         |         |    ○    |         |         |         |         |         |
| UC-004                   |         |         |         |    ○    |    ●    |         |         |         |
| UC-005                   |         |    ○    |         |    ●    |         |         |         |         |
| UC-006                   |         |         |    ●    |         |         |         |         |         |
| UC-007                   |         |         |         |         |    ●    |         |         |         |
| UC-008                   |         |         |         |         |    ●    |         |         |         |
| UC-009                   |         |         |         |         |         |    ●    |    ●    |         |
| UC-010                   |         |         |         |         |         |    ●    |         |         |
| UC-011                   |         |         |         |         |         |    ●    |         |         |
| UC-012                   |         |         |         |         |         |         |    ○    |         |
| UC-013                   |         |         |         |         |    ○    |         |    ●    |         |
| UC-014                   |         |         |         |         |         |         |    ●    |         |
| UC-015                   |         |         |         |         |         |         |    ○    |         |
| UC-016                   |         |         |         |         |         |         |    ●    |         |
| UC-017                   |         |         |         |         |         |         |    ●    |    ●    |
| UC-018                   |    ●    |         |         |         |         |         |         |         |
| UC-019                   |    ○    |         |         |         |         |         |         |    ○    |
| UC-020                   |         |         |         |         |         |         |         |    ●    |
| UC-021                   |    ○    |         |         |         |         |         |         |         |

## Diferencias con el análisis del docente

- **Cobertura.** Los ocho casos de uso del docente tienen al menos un caso de uso del proyecto con relación total, y
  los veintiún casos de uso del proyecto se relacionan con alguno del docente.
- **Granularidad.** Los casos de uso del docente agrupan varios objetivos y varios actores. El proyecto los separa en
  un caso de uso por objetivo de un actor, para que cada uno tenga un único actor principal, su propio flujo y sus
  propias pruebas. Por ejemplo, UCD-005 se reparte entre el estudiante (UC-004), el tutor (UC-007) y el miembro del
  tribunal (UC-008).
- **Habilitación académica (UCD-002).** El proyecto no tiene un paso de aprobación de la solicitud separado del
  inicio del trámite de titulación: el asistente administrativo verifica la documentación de requisitos al
  registrarla (UC-001), y la viabilidad académica se decide en la evaluación del perfil de proyecto (UC-005).
- **Reportes (UCD-008).** El proyecto ofrece estadísticas en pantalla (UC-017, UC-020) y la bitácora (UC-019), pero
  ningún reporte impreso o exportable. La elaboración de actas oficiales (FR-025) y el registro de pagos (FR-039)
  están en estado `Rejected` en el catálogo de requisitos.
- **Casos de uso propios del proyecto.** UC-012 (archivar), UC-015 (búsqueda global), UC-019 (bitácora), UC-020
  (estadísticas de acceso) y UC-021 (personalizar la interfaz) vienen de los lineamientos técnicos o del cierre del
  ciclo del trámite de titulación; el docente no los nombra, y aquí se ubican en el caso de uso del docente más
  cercano.
