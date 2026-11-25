# Requisitos: Seguimiento de Trámites de Titulación

Catálogo de requisitos del sistema de seguimiento del trámite de titulación por Proyecto de Grado de la carrera de
Ingeniería en Redes y Telecomunicaciones, derivado de [vision.md](vision.md). Los términos del dominio se definen en
[glossary.md](glossary.md).

Los valores de las columnas Priority y Status se escriben en inglés (High, Medium, Low; Open, In Progress,
Implemented, Verified, Deferred, Rejected), tal como los lee `/spec-review`.

## Requisitos funcionales

| ID     | Title                                   | User Story                                                                                                                                                                                   | Priority | Status   |
|--------|-----------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|----------|
| FR-001 | Registrar estudiante                    | Como asistente administrativo, quiero registrar al estudiante que inicia su trámite de titulación para que pueda dar seguimiento a su trámite en el sistema.                                 | High     | Open     |
| FR-002 | Registrar documentación de requisitos   | Como asistente administrativo, quiero adjuntar al trámite de titulación la boleta de inscripción, la hoja de vida y la carta de solicitud del estudiante para que la documentación de requisitos no dependa de una carpeta física. | High     | Open     |
| FR-003 | Registrar docente                       | Como asistente administrativo, quiero registrar a los docentes y al director de carrera para que puedan participar en los trámites de titulación con su propia cuenta.                       | High     | Open     |
| FR-004 | Asignar tutor                           | Como director de carrera, quiero asignar un tutor al estudiante que no propuso uno para que cada trámite de titulación tenga un docente que guíe el proyecto de grado.                        | High     | Open     |
| FR-005 | Presentar perfil de proyecto            | Como estudiante, quiero presentar mi perfil de proyecto en el sistema, también después de un rechazo, para que el revisor lo evalúe sin que yo vaya a la dirección de carrera.                | High     | Open     |
| FR-006 | Asignar revisor                         | Como director de carrera, quiero asignar un revisor al perfil de proyecto presentado para que su evaluación tenga un responsable.                                                            | High     | Open     |
| FR-007 | Evaluar perfil de proyecto              | Como revisor, quiero aprobar o rechazar un perfil de proyecto con las observaciones que lo justifican para que el estudiante sepa si su proyecto es viable para la modalidad de graduación.   | High     | Open     |
| FR-008 | Asignar tribunal evaluador              | Como director de carrera, quiero asignar tres docentes como tribunal evaluador a un perfil de proyecto aprobado para que el proyecto de grado tenga quién lo evalúe.                          | High     | Open     |
| FR-009 | Presentar entrega del proyecto de grado | Como estudiante, quiero subir una nueva entrega del documento de mi proyecto de grado para que mi tutor y el tribunal evaluador la revisen.                                                  | High     | Open     |
| FR-010 | Registrar observaciones del tribunal    | Como miembro del tribunal, quiero registrar observaciones sobre una entrega para que el estudiante sepa qué debe subsanar en su proyecto de grado.                                         | High     | Open     |
| FR-011 | Aprobar entrega para defensa            | Como miembro del tribunal, quiero aprobar una entrega sin observaciones pendientes para que el proyecto de grado pase a la pre-defensa.                                                     | High     | Open     |
| FR-012 | Registrar observaciones del tutor       | Como tutor, quiero registrar observaciones sobre las entregas de mis estudiantes para que mejoren el proyecto de grado antes de la revisión del tribunal evaluador.                         | Medium   | Open     |
| FR-013 | Consultar observaciones                 | Como estudiante, quiero ver las observaciones de cada entrega con su autor para que pueda subsanarlas en mi siguiente entrega.                                                              | High     | Open     |
| FR-014 | Consultar historial de entregas         | Como miembro del tribunal, quiero ver todas las entregas anteriores de un proyecto de grado con sus observaciones para que pueda comprobar si las observaciones fueron subsanadas.          | Medium   | Open     |
| FR-015 | Programar pre-defensa                   | Como director de carrera, quiero asignar la fecha y hora de la pre-defensa de un proyecto de grado aprobado para defensa para que el estudiante y el tribunal evaluador la conozcan.         | High     | Open     |
| FR-016 | Registrar observaciones de pre-defensa  | Como miembro del tribunal, quiero registrar las observaciones de la pre-defensa sobre el documento y el prototipo para que el estudiante las subsane antes de la defensa.                    | High     | Open     |
| FR-017 | Programar defensa                       | Como director de carrera, quiero asignar la fecha y hora de la defensa de un proyecto de grado después de su pre-defensa para que el estudiante y el tribunal evaluador la conozcan.         | High     | Open     |
| FR-018 | Ver calendario de defensas              | Como director de carrera, quiero ver en un calendario todas las pre-defensas y defensas programadas para que pueda asignar fechas sin superponer al tribunal evaluador.                      | Medium   | Open     |
| FR-019 | Registrar calificación                  | Como miembro del tribunal, quiero registrar la calificación conjunta de la defensa sobre 100 puntos para que el trámite de titulación quede concluido.                                    | High     | Open     |
| FR-020 | Archivar trámite                        | Como asistente administrativo, quiero archivar un trámite de titulación concluido para que deje de figurar entre los trámites en curso y su documentación quede disponible para consulta.  | Medium   | Open     |
| FR-021 | Recibir notificaciones como estudiante  | Como estudiante, quiero recibir en el sistema una notificación cuando mi trámite de titulación cambia de fase, recibe observaciones o tiene una fecha asignada para que no dependa del asistente administrativo para enterarme. | High     | Open     |
| FR-022 | Recibir notificaciones como docente     | Como docente, quiero recibir en el sistema una notificación cuando se me asigna un trámite de titulación o una entrega espera mi revisión para que pueda revisarla sin demora.            | High     | Open     |
| FR-023 | Consultar estado del trámite            | Como estudiante, quiero ver la fase actual de mi trámite de titulación y las fases ya concluidas para que sepa qué paso me corresponde.                                                     | High     | Open     |
| FR-024 | Consultar trámites en curso             | Como asistente administrativo, quiero ver la fase actual de todos los trámites de titulación para que pueda informar el estado de cualquiera cuando se me consulte.                         | High     | Open     |
| FR-025 | Elaborar actas oficiales                | Como asistente administrativo, quiero generar las actas oficiales de la defensa para que no las redacte a mano.                                                                            | Low      | Rejected |
| FR-026 | Almacenar prototipos                    | Como estudiante, quiero subir el prototipo de mi proyecto de grado para que el tribunal evaluador lo revise fuera de la pre-defensa.                                                       | Low      | Rejected |

FR-025 y FR-026 están fuera del alcance de la visión y quedan registrados como `Rejected`.

## Requisitos no funcionales

| ID      | Title                      | Requirement                                                                                                                              | Category     | Priority | Status |
|---------|----------------------------|------------------------------------------------------------------------------------------------------------------------------------------|--------------|----------|--------|
| NFR-001 | Tiempo de respuesta        | El 95 % de las cargas de página termina en menos de 3 segundos con 50 usuarios simultáneos.                                              | Performance  | High     | Open   |
| NFR-002 | Usuarios simultáneos       | El sistema atiende 50 usuarios simultáneos sin errores y cumpliendo NFR-001.                                                              | Scalability  | Medium   | Open   |
| NFR-003 | Disponibilidad             | El sistema está disponible el 99 % del tiempo de cada mes calendario, sin contar mantenimientos anunciados con 48 horas de anticipación. | Availability | Medium   | Open   |
| NFR-004 | Acceso por participación   | Un estudiante o docente solo puede ver los trámites de titulación en los que participa; el 100 % de los accesos a otro trámite se rechaza. | Security     | High     | Open   |
| NFR-005 | Acceso por rol             | Cada acción del sistema solo la ejecuta el rol que la tiene asignada en el catálogo; el 100 % de los intentos de otro rol se rechaza.    | Security     | High     | Open   |
| NFR-006 | Autenticación              | Todo acceso requiere iniciar sesión; tras 5 intentos fallidos en 1 minuto, la cuenta queda bloqueada para nuevos intentos durante 1 minuto. | Security     | High     | Open   |
| NFR-007 | Cifrado en tránsito        | Todo el tráfico entre el navegador y el servidor usa HTTPS con TLS 1.2 o superior.                                                        | Security     | High     | Open   |
| NFR-008 | Conservación de entregas   | Ninguna entrega ni documento de requisitos se borra; el 100 % de las versiones subidas se puede descargar mientras el trámite exista.    | Availability | High     | Open   |
| NFR-009 | Idioma                     | El 100 % de los textos de la interfaz está en español.                                                                                   | Usability    | High     | Open   |
| NFR-010 | Pantallas pequeñas         | Todas las pantallas se pueden usar sin desplazamiento horizontal en un ancho de 360 píxeles.                                              | Usability    | Medium   | Open   |

## Restricciones

| ID    | Title                   | Constraint                                                                                                  | Category  | Priority | Status |
|-------|-------------------------|-------------------------------------------------------------------------------------------------------------|-----------|----------|--------|
| C-001 | Plataforma del servidor | El servidor se construye con Laravel 13 sobre PHP 8.3 o superior.                                           | Technical | High     | Open   |
| C-002 | Plataforma del cliente  | La interfaz se construye con React 19 e Inertia 3.                                                          | Technical | High     | Open   |
| C-003 | Navegadores             | La interfaz funciona en las dos últimas versiones de Chrome, Firefox y Edge.                                | Technical | High     | Open   |
| C-004 | Formato de documentos   | Las entregas y la documentación de requisitos solo se aceptan en PDF, de hasta 20 MB por archivo.          | Technical | High     | Open   |
| C-005 | Notificaciones internas | Las notificaciones solo se muestran dentro del sistema; no se envían por correo electrónico ni por otro canal. | Business  | High     | Open   |
| C-006 | Composición del tribunal | El tribunal evaluador está compuesto por exactamente tres docentes de la carrera.                          | Business  | High     | Open   |
| C-007 | Escala de calificación  | La calificación de la defensa es un número entero entre 0 y 100.                                            | Business  | High     | Open   |
