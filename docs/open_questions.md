# Open Questions

Questions about the specification that the specification does not answer yet. A question is `Resolved` only when
the answer is in the specification itself; **Resolved in** names where. Resolved and withdrawn questions stay as
history.

## OQ-001: ¿Quién asigna el revisor a un perfil de proyecto?

**Element:** FR-006  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** El director de carrera asigna el revisor, como asigna el tutor y el tribunal evaluador.  

La visión dice que el revisor evalúa el perfil de proyecto, pero no quién lo designa. Lecturas: (a) el director de
carrera; (b) un revisor fijo de la carrera evalúa todos los perfiles; (c) el tutor sugiere y el director confirma.

**Answer:** el revisor es el docente de taller de grado de la carrera, actualmente solo existe uno.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** FR-006, Glossary: Revisor, Glossary: Docente de taller de grado  

## OQ-002: ¿Qué disponibilidad mensual debe tener el sistema?

**Element:** NFR-003  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** 99 % de cada mes calendario, sin contar mantenimientos anunciados con 48 horas de anticipación.  

La visión no fija un umbral de disponibilidad. El valor depende de dónde se instale el sistema (servidor de la
universidad o un servicio externo).

**Answer:** corre y vale.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** NFR-003  

## OQ-003: ¿Qué navegadores debe soportar la interfaz?

**Element:** C-003  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Las dos últimas versiones de Chrome, Firefox y Edge.  

La visión no nombra navegadores. Si la universidad usa equipos con otro navegador (por ejemplo Safari), hay que
añadirlo.

**Answer:** Chrome, Firefox, Edge  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** C-003  

## OQ-004: ¿La calificación de la defensa admite decimales?

**Element:** C-007  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Un número entero entre 0 y 100.  

La visión dice que la calificación es "sobre 100 puntos", sin decir si puede tener decimales ni cuál es la
calificación mínima de aprobación.

**Answer:** no.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** C-007  

## OQ-005: ¿Cuántos intentos fallidos de inicio de sesión bloquean la cuenta, y por cuánto tiempo?

**Element:** NFR-006  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** 5 intentos fallidos en 1 minuto bloquean nuevos intentos durante 1 minuto.  

La visión no trata la seguridad del inicio de sesión. El valor asumido es el límite habitual del marco de trabajo
del proyecto.

**Answer:** 5, por 30 min.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** NFR-006  

## OQ-006: ¿Qué ancho mínimo de pantalla debe soportar la interfaz?

**Element:** NFR-010  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** 360 píxeles, el ancho de un teléfono común.  

Los estudiantes y docentes pueden consultar observaciones desde el teléfono; la visión no dice si el uso en
teléfono es necesario.

**Answer:** 360 pixeles.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** NFR-010  

## OQ-007: ¿En qué horario cambia automáticamente el modo día/noche?

**Element:** FR-032  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Modo día de 07:00 a 19:00 y modo noche el resto del día, según la hora del dispositivo del usuario.  

Los lineamientos técnicos (3.5) piden conmutación automática o manual según horario, sin fijar el horario.

**Answer:** el tema cambia con el modo del sistema operativo  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** FR-032  

## OQ-008: ¿Qué nivel de WCAG significa "WCAG compliance básico"?

**Element:** NFR-014  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** WCAG 2.1 nivel AA: contraste de al menos 4,5:1 en todos los temas y de 7:1 en el modo de alto contraste.  

Los lineamientos técnicos (3.5) piden "WCAG compliance básico" sin nombrar versión ni nivel. Lecturas: (a) WCAG 2.1
nivel A; (b) WCAG 2.1 nivel AA, el nivel que suelen exigir las normas de accesibilidad.

**Answer:** está bien  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** NFR-014  

## OQ-009: ¿Qué métricas muestra el panel de estadísticas de trámites?

**Element:** FR-036  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Cantidad de trámites de titulación por fase, defensas programadas y calificación promedio.  

Los lineamientos técnicos (3.8) piden "métricas clave relacionadas con la actividad del negocio" sin nombrarlas.
Otras posibles: tiempo promedio por fase, perfiles de proyecto rechazados, observaciones por versión del proyecto.

**Answer:** Solo las de FR-036.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** FR-036, UC-017 BR-001  

## OQ-010: ¿Cuál es el número de grupo en la dirección de despliegue?

**Element:** C-012  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** La dirección queda como en los lineamientos técnicos, con `grupoXXsc`, hasta conocer el número de grupo.  

Los lineamientos técnicos (4) dan la dirección con `XX` en lugar del número de grupo. El número también identifica
el esquema asignado al grupo en db.tecnoweb.org.bo (C-008).

**Answer:** 03  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** C-012, C-008  

## OQ-011: ¿Con qué datos se identifica a un estudiante?

**Element:** Entity: STUDENT.registration_number  
**Raised by:** /entity-model, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Número de registro universitario y número de carnet de identidad, ambos obligatorios y únicos; teléfono opcional.  

Ni la visión ni el catálogo dicen qué datos personales del estudiante registra el asistente administrativo (FR-001).
La carta de solicitud trae "sus datos personales", sin enumerarlos.

**Answer:** los estudiantes cuentan con un número de registro y adicionalmente su cédula de Identidad, la cédula de identidad, esta compuesta por un número y un complemento opcional. El número de complemento, se asigna en Bolivia cuando por algún error el SEGIP (Servicio General de Identificación Personal) asigna el mismo número a 2 ciudadanos, entonces el complemento es para diferenciarlos.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** Entity: STUDENT.registration_number, Entity: STUDENT.id_card_number, Entity: STUDENT.id_card_complement  

## OQ-012: ¿El perfil de proyecto se presenta como archivo PDF o como formulario?

**Element:** Entity: PROJECT_VERSION.file_path  
**Raised by:** /entity-model, 2026-11-25  
**Blocking:** No  
**Status:** Withdrawn  
**Assumed:** El estudiante escribe el título y sube el perfil de proyecto como un PDF de hasta 20 MB (C-004).  

Lecturas: (a) título y PDF; (b) un formulario con campos (objetivos, alcance, justificación) sin archivo.

**Withdrawn:** El perfil de proyecto pasó a ser la versión del proyecto de grado de la fase Evaluación de perfil, que siempre es un archivo PDF (PROJECT_VERSION); la lectura (b) ya no aplica.  

## OQ-013: ¿Entre qué valores se puede ajustar el tamaño de la fuente?

**Element:** Entity: USER_PREFERENCE.font_size_percent  
**Raised by:** /entity-model, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Del 75 % al 200 % del tamaño normal; el 200 % es el máximo que exige NFR-015.  

FR-033 permite aumentar o reducir la fuente sin fijar los límites.

**Answer:** Del 75 % al 200 %.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** Entity: USER_PREFERENCE.font_size_percent  

## OQ-014: ¿Qué longitud máxima tiene una observación?

**Element:** Entity: OBSERVATION.body  
**Raised by:** /entity-model, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** 2000 caracteres.  

Las observaciones del revisor, del tutor y del tribunal evaluador son texto libre; ningún requisito fija su largo.

**Answer:** 2000 caracteres.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** Entity: OBSERVATION.body  

## OQ-015: ¿La contraseña inicial del estudiante incluye el complemento de su cédula de identidad?

**Element:** UC-001 BR-005  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Sí: el número de cédula de identidad seguido de su complemento cuando lo tiene.  

La contraseña inicial es el número de cédula de identidad, pero dos estudiantes pueden compartir el número y
distinguirse solo por el complemento que asigna el SEGIP.

**Answer:** no  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-001 BR-005  

## OQ-016: ¿El sistema debe exigir un plazo entre un trámite de titulación reprobado y el siguiente?

**Element:** UC-001 BR-003  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** No: el estudiante que reprobó espera al siguiente semestre, y su nueva boleta de inscripción lo respalda; el sistema no comprueba el semestre.  

El estudiante que reprueba la defensa formal debe esperar al siguiente semestre para volver a presentar los
requisitos, pero el sistema no registra semestres.

**Answer:** No está dentro del alcance  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-001 BR-003  

## OQ-017: ¿A quién se notifica un cambio de tutor?

**Element:** UC-002 A1  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Al nuevo tutor, al tutor anterior y al estudiante.  

FR-021 notifica al estudiante los cambios de fase, observaciones y fechas, y FR-022 al docente que recibe un trámite;
ningún requisito dice quién se entera de que un tutor deja de serlo.

**Answer:** al estudiante; al aclarar frente a FR-022: al estudiante y al nuevo tutor, el tutor anterior no recibe notificación.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-002 A1  

## OQ-018: ¿El docente de taller de grado puede revisar el perfil de proyecto de un estudiante del que es tutor?

**Element:** UC-003 BR-004  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Sí: el docente de taller de grado revisa todos los perfiles de proyecto, también los de sus propios estudiantes.  

El docente de taller de grado revisa todo perfil de proyecto, y puede a la vez ser tutor de algún estudiante; ningún
requisito dice si ese doble papel es aceptable.

**Answer:** el docente de taller de grado no puede ser tutor.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-001 BR-006, UC-002 BR-001, UC-003 BR-002  

## OQ-019: ¿Cuántas versiones puede presentar el estudiante en la fase defensa formal?

**Element:** UC-004 BR-006  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Varias, mientras la defensa formal no se realizó; la última presentada es la que se defiende.  

Las versiones de la fase defensa formal no se revisan antes de la defensa formal, así que ninguna evaluación limita
cuántas puede subir el estudiante.

**Answer:** no hay límite mediante hayan observaciones  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-004 BR-002, UC-004 BR-006, UC-004 A3  

## OQ-020: ¿A quién se notifica la aprobación de un perfil de proyecto?

**Element:** UC-005 step 8  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Al estudiante y al director de carrera, que debe asignar el tribunal evaluador; el tutor no recibe notificación.  

FR-021 notifica al estudiante los cambios de su trámite de titulación, pero ningún requisito dice cómo se entera el
director de carrera de que un perfil de proyecto aprobado espera la asignación del tribunal evaluador.

**Answer:** al estudiante  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-005 step 8  

## OQ-021: ¿El revisor puede registrar observaciones al aprobar un perfil de proyecto?

**Element:** UC-005 BR-002  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Sí, son opcionales al aprobar; al rechazar se exige al menos una.  

FR-007 pide aprobar o rechazar "con las observaciones que lo justifican", sin decir si una aprobación también las
lleva.

**Answer:** sí, puede  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-005 BR-002  

## OQ-022: ¿Cuenta la aprobación de un miembro del tribunal que fue reemplazado?

**Element:** UC-006 BR-004  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** No: solo cuentan las aprobaciones de los tres miembros vigentes, y el nuevo miembro revisa la versión del proyecto en revisión.  

Un miembro puede ser reemplazado después de aprobar una versión de la fase pre-defensa que los otros dos aún no
aprobaron; ningún requisito dice si su aprobación sigue valiendo.

**Answer:** ok  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-006 BR-004, UC-008 BR-004  

## OQ-023: ¿Un miembro del tribunal puede cambiar su pronunciamiento antes de que los otros dos se pronuncien?

**Element:** UC-008 BR-002  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** No: el pronunciamiento registrado queda fijo, como la evaluación del perfil de proyecto (UC-005 BR-003).  

Una versión de la fase pre-defensa sigue en revisión hasta que los tres miembros se pronuncian; ningún requisito dice
si mientras tanto un miembro puede pasar de observar a aprobar, o al revés.

**Answer:** No; el pronunciamiento queda fijo  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-008 BR-002  

## OQ-024: ¿A quién se notifica que el tribunal aprobó una versión de la fase pre-defensa?

**Element:** UC-008 step 10  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Solo al estudiante, como la aprobación del perfil de proyecto (OQ-020); el director de carrera ve el trámite pendiente al programar la pre-defensa.  

FR-015 pide al director de carrera programar la pre-defensa cuando los tres miembros aprobaron, pero ningún requisito
dice cómo se entera.

**Answer:** Solo al estudiante  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-008 step 10  

## OQ-025: ¿A quién se notifica la fecha de una pre-defensa o defensa formal?

**Element:** UC-009 step 9  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Al estudiante, a su tutor y a los miembros vigentes de su tribunal evaluador, también cuando se reprograma.  

FR-021 notifica al estudiante cuando su trámite tiene una fecha asignada; FR-022 solo cubre a los docentes cuando se
les asigna un trámite o una versión espera su revisión, no las fechas.

**Answer:** Al estudiante, a su tutor y al tribunal vigente, también al reprogramar  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-009 step 9  

## OQ-026: ¿El choque de horario considera también al tutor y al ambiente?

**Element:** UC-009 BR-003  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Sí: además de los miembros del tribunal, no se cruzan el tutor ni el ambiente, cuando el ambiente está indicado.  

FR-018 busca no superponer al tribunal evaluador; el tutor y el ambiente también suelen estar presentes en la
pre-defensa y la defensa formal.

**Answer:** ok  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-009 BR-003  

## OQ-027: ¿Basta con un miembro del tribunal para registrar la inasistencia del estudiante?

**Element:** UC-010 BR-004  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Sí: la inasistencia es un hecho que cualquier miembro vigente del tribunal registra, y con ella el trámite de titulación queda abandonado.  

Cada miembro registra sus propias observaciones de la pre-defensa, pero la inasistencia afecta a todo el tribunal y
abandona el trámite de titulación.

**Answer:** si  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-010 BR-004, UC-011 BR-005  

## OQ-028: ¿El tutor recibe las observaciones de la pre-defensa?

**Element:** UC-010 step 10  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Sí: el estudiante y su tutor reciben la notificación con las observaciones de la pre-defensa.  

FR-021 notifica al estudiante cuando recibe observaciones; ningún requisito dice si el tutor, que lo guía para la
defensa formal, también se entera.

**Answer:** si  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-010 step 10  

## OQ-029: ¿Cómo se redondea el promedio de las calificaciones de los tres miembros del tribunal?

**Element:** UC-011 BR-003  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** Al entero más cercano, con las mitades hacia arriba: un promedio de 50,5 da 51 y aprueba.  

La calificación conjunta es el promedio de tres enteros, que puede tener decimales; el redondeo decide la aprobación
cerca del umbral de 51 (C-016).

**Answer:** entero mas cercano, mitades hacia arriba.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-011 BR-003  

## OQ-030: ¿Un miembro ve las calificaciones de los demás antes de registrar la suya?

**Element:** UC-011 BR-003  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** No: ve qué miembros ya calificaron, pero no sus calificaciones, hasta que se calcula la calificación conjunta.  

Ver las calificaciones de los demás puede influir en la propia; ningún requisito lo dice.

**Answer:** ok  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-011 BR-003  

## OQ-031: ¿Puede haber más de un director de carrera activo?

**Element:** UC-018 BR-002  
**Raised by:** /use-case-spec, 2026-11-25  
**Blocking:** No  
**Status:** Resolved  
**Assumed:** No: hay a lo sumo un director de carrera activo; para cambiarlo, primero se le quita el rol al actual.  

El glosario habla de "la autoridad de la carrera" en singular, pero ningún requisito limita cuántas cuentas tienen el
rol director de carrera.

**Answer:** no.  
**Answered by:** Roger Roca Arano, 2026-11-25  
**Resolved in:** UC-018 BR-002  
