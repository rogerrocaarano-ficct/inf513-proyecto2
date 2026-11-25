# Requisitos: Seguimiento de Trámites de Titulación

Catálogo de requisitos del sistema de seguimiento del trámite de titulación por Proyecto de Grado de la carrera de
Ingeniería en Redes y Telecomunicaciones, derivado de [vision.md](vision.md) y de los [lineamientos técnicos](lineamientos_tecnicos.md). Los términos del dominio se definen en
[glossary.md](glossary.md).

Los valores de las columnas Priority y Status se escriben en inglés (High, Medium, Low; Open, In Progress,
Implemented, Verified, Deferred, Rejected), tal como los lee `/spec-review`.

## Requisitos funcionales

| ID     | Title                                   | User Story                                                                                                                                                                                   | Priority | Status   |
|--------|-----------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------|----------|
| FR-001 | Registrar estudiante                    | Como asistente administrativo, quiero registrar al estudiante que inicia su trámite de titulación para que pueda dar seguimiento a su trámite en el sistema.                                 | High     | Open     |
| FR-002 | Registrar documentación de requisitos   | Como asistente administrativo, quiero adjuntar al trámite de titulación la boleta de inscripción, la hoja de vida y la carta de solicitud del estudiante para que la documentación de requisitos no dependa de una carpeta física. | High     | Open     |
| FR-003 | Registrar personal de la carrera        | Como administrador del sistema, quiero registrar a los docentes, al director de carrera y a los asistentes administrativos para que puedan participar en los trámites de titulación con su propia cuenta. | High     | Open     |
| FR-004 | Asignar tutor                           | Como director de carrera, quiero asignar un tutor al estudiante que no propuso uno para que cada trámite de titulación tenga un docente que guíe el proyecto de grado.                        | High     | Open     |
| FR-005 | Presentar perfil de proyecto            | Como estudiante, quiero subir una versión de mi proyecto de grado en la fase evaluación de perfil, también después de un rechazo, para que el revisor la evalúe sin que yo vaya a la dirección de carrera. | High     | Open     |
| FR-006 | Designar docente de taller de grado     | Como director de carrera, quiero designar al docente de taller de grado para que todo perfil de proyecto presentado le llegue como revisor sin asignarlo uno por uno.                        | High     | Open     |
| FR-007 | Evaluar perfil de proyecto              | Como revisor, quiero aprobar o rechazar un perfil de proyecto con las observaciones que lo justifican para que el estudiante sepa si su proyecto es viable para la modalidad de graduación.   | High     | Open     |
| FR-008 | Asignar tribunal evaluador              | Como director de carrera, quiero asignar tres docentes como tribunal evaluador a un perfil de proyecto aprobado para que el proyecto de grado tenga quién lo evalúe.                          | High     | Open     |
| FR-009 | Presentar versión del proyecto          | Como estudiante, quiero subir una nueva versión de mi proyecto de grado en la fase pre-defensa o defensa formal para que mi tutor y el tribunal evaluador la revisen.                       | High     | Open     |
| FR-010 | Registrar observaciones del tribunal    | Como miembro del tribunal, quiero registrar observaciones sobre una versión del proyecto de la fase pre-defensa para que el estudiante sepa qué debe subsanar en su proyecto de grado.     | High     | Open     |
| FR-011 | Aprobar versión para pre-defensa        | Como miembro del tribunal, quiero aprobar una versión del proyecto de la fase pre-defensa para que, con la aprobación de los tres miembros, el director de carrera pueda programar la pre-defensa. | High     | Open     |
| FR-012 | Registrar observaciones del tutor       | Como tutor, quiero registrar observaciones sobre las versiones del proyecto de mis estudiantes para que mejoren el proyecto de grado antes de la revisión del tribunal evaluador.            | Medium   | Open     |
| FR-013 | Consultar observaciones                 | Como estudiante, quiero ver las observaciones de cada versión del proyecto con su autor para que pueda subsanarlas en mi siguiente versión.                                                | High     | Open     |
| FR-014 | Consultar historial como tribunal       | Como miembro del tribunal, quiero ver todas las versiones anteriores de un proyecto de grado con su fase y sus observaciones para que pueda comprobar si las observaciones fueron subsanadas. | Medium   | Open     |
| FR-015 | Programar pre-defensa                   | Como director de carrera, quiero asignar la fecha y hora de la pre-defensa de un proyecto de grado cuya versión de la fase pre-defensa aprobaron los tres miembros del tribunal para que el estudiante y el tribunal evaluador la conozcan. | High     | Open     |
| FR-016 | Registrar observaciones de pre-defensa  | Como miembro del tribunal, quiero registrar las observaciones de la pre-defensa sobre el documento y el prototipo para que el estudiante las subsane antes de la defensa.                    | High     | Open     |
| FR-017 | Programar defensa formal                | Como director de carrera, quiero asignar la fecha y hora de la defensa formal de un proyecto de grado después de su pre-defensa para que el estudiante y el tribunal evaluador la conozcan.  | High     | Open     |
| FR-018 | Ver calendario de defensas              | Como director de carrera, quiero ver en un calendario todas las pre-defensas y defensas formales programadas para que pueda asignar fechas sin superponer al tribunal evaluador.            | Medium   | Open     |
| FR-019 | Registrar calificación                  | Como miembro del tribunal, quiero registrar la calificación conjunta de la defensa formal sobre 100 puntos para que el trámite de titulación quede concluido.                              | High     | Open     |
| FR-020 | Archivar trámite                        | Como asistente administrativo, quiero archivar un trámite de titulación concluido para que deje de figurar entre los trámites en curso y su documentación quede disponible para consulta.  | Medium   | Open     |
| FR-021 | Recibir notificaciones como estudiante  | Como estudiante, quiero recibir en el sistema una notificación cuando mi trámite de titulación cambia de fase, recibe observaciones o tiene una fecha asignada para que no dependa del asistente administrativo para enterarme. | High     | Open     |
| FR-022 | Recibir notificaciones como docente     | Como docente, quiero recibir en el sistema una notificación cuando se me asigna un trámite de titulación o una versión del proyecto espera mi revisión para que pueda revisarla sin demora. | High     | Open     |
| FR-023 | Consultar estado del trámite            | Como estudiante, quiero ver la fase actual de mi trámite de titulación y las fases ya concluidas para que sepa qué paso me corresponde.                                                     | High     | Open     |
| FR-024 | Consultar trámites en curso             | Como asistente administrativo, quiero ver la fase actual de todos los trámites de titulación para que pueda informar el estado de cualquiera cuando se me consulte.                         | High     | Open     |
| FR-025 | Elaborar actas oficiales                | Como asistente administrativo, quiero generar las actas oficiales de la defensa formal para que no las redacte a mano.                                                                     | Low      | Rejected |
| FR-026 | Almacenar prototipos                    | Como estudiante, quiero subir el prototipo de mi proyecto de grado para que el tribunal evaluador lo revise fuera de la pre-defensa.                                                       | Low      | Rejected |
| FR-027 | Asignar rol                             | Como administrador del sistema, quiero asignar a cada usuario que registro uno de los roles que el sistema define para que acceda solo a lo que su rol permite.                              | High     | Open     |
| FR-028 | Configurar menú                         | Como administrador del sistema, quiero definir qué opciones de menú habilita cada permiso para que el menú de cada rol se arme desde la base de datos sin cambiar el código.                | Medium   | Rejected |
| FR-029 | Ver menú según rol                      | Como usuario, quiero ver en la barra de navegación solo las opciones que los permisos de mi rol habilitan para que no encuentre opciones que no puedo usar.                                | High     | Open     |
| FR-030 | Consultar bitácora                      | Como administrador del sistema, quiero consultar la bitácora de inicios de sesión exitosos, intentos fallidos y direcciones IP, filtrada por fecha y usuario, para que pueda detectar accesos indebidos. | High     | Open     |
| FR-031 | Elegir tema                             | Como usuario, quiero elegir entre los temas Niños, Jóvenes y Adultos para que la interfaz se adapte a mi preferencia.                                                                       | High     | Open     |
| FR-032 | Cambiar modo día/noche                  | Como usuario, quiero que el modo día o noche siga automáticamente el modo claro u oscuro de mi sistema operativo, y poder alternarlo a mano, para que la pantalla sea cómoda de leer a cualquier hora. | High     | Open     |
| FR-033 | Cambiar tamaño de fuente                | Como usuario, quiero aumentar o reducir el tamaño de la fuente de la interfaz para que pueda leer el contenido sin esfuerzo.                                                               | High     | Open     |
| FR-034 | Alternar alto contraste                 | Como usuario, quiero activar o desactivar un modo de alto contraste para que pueda distinguir el contenido si tengo baja visión.                                                           | High     | Open     |
| FR-035 | Ver contador de visitas                 | Como usuario, quiero ver en el pie de página el número de visitas de la página que estoy viendo para que conozca cuánto se consulta.                                                       | High     | Open     |
| FR-036 | Ver estadísticas de trámites            | Como director de carrera, quiero ver un panel de estadísticas con la cantidad de trámites de titulación por fase, las defensas formales programadas y la calificación promedio para que pueda evaluar el avance de la carrera. | High     | Open     |
| FR-037 | Ver estadísticas de acceso              | Como administrador del sistema, quiero ver un panel de estadísticas con los inicios de sesión, los intentos fallidos y las páginas más visitadas por día para que pueda vigilar el flujo de accesos. | High     | Open     |
| FR-038 | Búsqueda global                         | Como usuario, quiero buscar trámites de titulación por nombre del estudiante o título del proyecto de grado desde el encabezado para que llegue a un trámite sin recorrer listas.          | High     | Open     |
| FR-039 | Registrar pagos                         | Como asistente administrativo, quiero registrar los pagos del trámite de titulación para que conste qué pagó cada estudiante.                                                             | Low      | Rejected |
| FR-040 | Consultar historial como estudiante     | Como estudiante, quiero ver y descargar todas las versiones de mi proyecto de grado, desde el perfil de proyecto, con su fase y sus observaciones para que pueda seguir cómo evolucionó.  | Medium   | Open     |
| FR-041 | Consultar historial como revisor        | Como revisor, quiero ver las versiones rechazadas del perfil de proyecto de un estudiante con sus observaciones para que pueda evaluar la nueva versión frente a las anteriores.          | Medium   | Open     |

FR-025 y FR-026 están fuera del alcance de la visión, y FR-039 (el módulo de pagos que menciona el diagrama de los
lineamientos técnicos) fuera del alcance del proyecto; quedan registrados como `Rejected`.

Los roles, sus permisos y las opciones de menú que habilita cada permiso son fijos: el sistema los define y los
carga en la base de datos, y el administrador del sistema no los modifica. Por eso FR-028 queda como `Rejected`, y
FR-027 se limita a asignar uno de esos roles a cada usuario. El menú de FR-029 se sigue armando desde la base de
datos según los permisos del rol activo.

## Requisitos no funcionales

| ID      | Title                      | Requirement                                                                                                                              | Category     | Priority | Status |
|---------|----------------------------|------------------------------------------------------------------------------------------------------------------------------------------|--------------|----------|--------|
| NFR-001 | Tiempo de respuesta        | El 95 % de las cargas de página termina en menos de 3 segundos con 50 usuarios simultáneos.                                              | Performance  | High     | Open   |
| NFR-002 | Usuarios simultáneos       | El sistema atiende 50 usuarios simultáneos sin errores y cumpliendo NFR-001.                                                              | Scalability  | Medium   | Open   |
| NFR-003 | Disponibilidad             | El sistema está disponible el 99 % del tiempo de cada mes calendario, sin contar mantenimientos anunciados con 48 horas de anticipación. | Availability | Medium   | Open   |
| NFR-004 | Acceso por participación   | Un estudiante o docente solo puede ver los trámites de titulación en los que participa; el 100 % de los accesos a otro trámite se rechaza. | Security     | High     | Open   |
| NFR-005 | Acceso por rol             | Cada acción del sistema solo la ejecuta el rol que la tiene asignada en el catálogo; el 100 % de los intentos de otro rol se rechaza.    | Security     | High     | Open   |
| NFR-006 | Autenticación              | Todo acceso requiere iniciar sesión; tras 5 intentos fallidos consecutivos, la cuenta queda bloqueada para nuevos intentos durante 30 minutos. | Security     | High     | Open   |
| NFR-007 | Cifrado en tránsito        | Todo el tráfico entre el navegador y el servidor usa HTTPS con TLS 1.2 o superior.                                                        | Security     | High     | Open   |
| NFR-008 | Conservación de versiones  | Ninguna versión del proyecto ni documento de requisitos se borra; el 100 % de los archivos subidos se puede descargar mientras el trámite exista. | Availability | High     | Open   |
| NFR-009 | Idioma                     | El 100 % de los textos de la interfaz, incluidos los mensajes de error de validación, está en español.                                  | Usability    | High     | Open   |
| NFR-010 | Pantallas pequeñas         | Todas las pantallas se pueden usar sin desplazamiento horizontal en un ancho de 360 píxeles.                                              | Usability    | Medium   | Open   |
| NFR-011 | Bitácora de sesiones       | El 100 % de los inicios de sesión exitosos y fallidos se guarda en la base de datos con fecha, hora, usuario y dirección IP.             | Security     | High     | Open   |
| NFR-012 | Bitácora de accesos        | El 100 % de las visitas a páginas se guarda en la base de datos con fecha, hora, página, usuario y dirección IP.                         | Security     | High     | Open   |
| NFR-013 | Validación de entradas     | El 100 % de los formularios valida sus campos en el cliente y en el servidor; el servidor rechaza toda entrada inválida aunque el cliente no la haya validado. | Security     | High     | Open   |
| NFR-014 | Contraste                  | Todos los temas cumplen una relación de contraste de al menos 4,5:1 para texto normal (WCAG 2.1 AA), y el modo de alto contraste de al menos 7:1. | Usability    | High     | Open   |
| NFR-015 | Escalado tipográfico       | La interfaz se puede usar con la fuente ampliada hasta el 200 % sin pérdida de contenido ni de funciones (WCAG 2.1, criterio 1.4.4).    | Usability    | High     | Open   |

## Restricciones

| ID    | Title                    | Constraint                                                                                                       | Category   | Source                          | Priority | Status |
|-------|--------------------------|------------------------------------------------------------------------------------------------------------------|------------|---------------------------------|----------|--------|
| C-001 | Plataforma del servidor  | El servidor se construye con Laravel 13 sobre PHP 8.3 o superior.                                                | Regulatory | Lineamientos técnicos, 3.3      | High     | Open   |
| C-002 | Plataforma del cliente   | La interfaz se construye con React 19 y se comunica con el servidor mediante Inertia 3.                         | Regulatory | Lineamientos técnicos, 2        | High     | Open   |
| C-003 | Navegadores              | La interfaz funciona en las dos últimas versiones de Chrome, Firefox y Edge.                                     | Technical  |                                 | High     | Open   |
| C-004 | Formato de documentos    | Las versiones del proyecto y la documentación de requisitos solo se aceptan en PDF, de hasta 20 MB por archivo.  | Technical  |                                 | High     | Open   |
| C-005 | Notificaciones internas  | Las notificaciones solo se muestran dentro del sistema; no se envían por correo electrónico ni por otro canal.   | Business   |                                 | High     | Open   |
| C-006 | Composición del tribunal | El tribunal evaluador está compuesto por exactamente tres docentes de la carrera.                               | Business   |                                 | High     | Open   |
| C-007 | Escala de calificación   | La calificación de la defensa formal es un número entero entre 0 y 100.                                          | Business   |                                 | High     | Open   |
| C-008 | Base de datos            | Los datos se guardan en PostgreSQL, en el esquema asignado al grupo 03 del servidor db.tecnoweb.org.bo.          | Regulatory | Lineamientos técnicos, 2, 4     | High     | Open   |
| C-009 | Arquitectura en 3 capas  | El sistema se divide en capa de presentación, capa de lógica de negocio y capa de persistencia.                  | Regulatory | Lineamientos técnicos, 2        | High     | Open   |
| C-010 | Roles de negocio         | El sistema tiene al menos dos roles de negocio además del rol de administrador del sistema.                      | Regulatory | Lineamientos técnicos, 3.2      | High     | Open   |
| C-011 | Identidad visual         | Todas las pantallas usan la misma hoja de estilos Tailwind y una maquetación coherente con los lineamientos presentados en las clases teóricas. | Regulatory | Lineamientos técnicos, 3.1, 3.5 | High     | Open   |
| C-012 | Despliegue               | El sistema se publica en el servidor web institucional, en https://www.tecnoweb.org.bo/inf513/grupo03sc/proyecto2. | Regulatory | Lineamientos técnicos, 4        | High     | Open   |
| C-013 | Control de versiones     | El código fuente se mantiene en un repositorio de GitHub.                                                        | Regulatory | Lineamientos técnicos, 4        | High     | Open   |
| C-014 | Almacenamiento de archivos | Los archivos subidos se guardan en el almacenamiento de archivos del servidor, nunca en la base de datos; la base de datos guarda solo su ruta. | Technical  |                                 | High     | Open   |
| C-015 | Fecha límite             | Un equipo de tres desarrolladores publica el sistema a más tardar el 30 de noviembre de 2026.                    | Schedule   |                                 | High     | Open   |
