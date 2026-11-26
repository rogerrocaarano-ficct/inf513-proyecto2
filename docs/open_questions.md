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

## OQ-032: ¿Con qué textos notifica y confirma el sistema al iniciar un trámite de titulación?

**Element:** UC-001  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El paso 12 notifica al tutor y el paso 13 confirma el inicio, y A1, A3, A7 y A9 informan o señalan un dato, pero la
especificación no fija los textos. Lectura propuesta: un catálogo de mensajes en español con los términos del
glosario.

**Answer:** paso 12 (al tutor): "Se le asignó como tutor del trámite de titulación de {estudiante}."; paso 13:
"Trámite de titulación iniciado." y, para una cuenta nueva, "La contraseña inicial del estudiante es su número de
cédula de identidad."; A1: "{nombre} ya tiene un trámite de titulación en curso, en la fase {fase}."; A3: "{nombre}
ya aprobó su trámite de titulación."; A7: "El correo electrónico ya pertenece a otro usuario." y "La cédula de
identidad ya pertenece a otro estudiante."; A9: "Adjunte la {documento}.", "El archivo debe ser PDF.", "El archivo
no debe superar los 20 MB."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-033: ¿Con qué textos notifica y confirma el sistema al asignar o cambiar el tutor?

**Element:** UC-002  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

Los pasos 8 y 9, A1 step 5, A3 y A4 notifican, confirman o informan, pero la especificación no fija los textos.

**Answer:** paso 8 y A1 step 5 (al tutor): "Se le asignó como tutor del trámite de titulación de {estudiante}.";
paso 8 (al estudiante): "Su trámite de titulación pasó a la fase evaluación de perfil con {tutor} como tutor.";
A1 step 5 (al estudiante): "Su trámite de titulación tiene un nuevo tutor: {tutor}."; paso 9: "Tutor asignado:
{tutor}. Fase: {fase}."; A3: "No hay trámites de titulación en curso."; A4: "{docente} tiene la {acto} de
{estudiante} el {fecha y hora}, que se cruza con la {acto} de este trámite. Reprograme antes el acto o elija otro
docente."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-034: ¿Con qué textos notifica y confirma el sistema al designar al docente de taller de grado?

**Element:** UC-003  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

Los pasos 7 y 8 y A1 step 3 notifican y confirman, pero la especificación no fija los textos.

**Answer:** paso 7 y A1 step 3 (al nuevo): "Se le designó como docente de taller de grado; recibe {n} perfiles de
proyecto para revisar."; paso 7 (al anterior): "Dejó de ser docente de taller de grado; sus perfiles de proyecto en
revisión pasaron a {nuevo}."; paso 8: "{docente} es el docente de taller de grado; {n} perfiles de proyecto pasaron a
su revisión."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-035: ¿Con qué textos notifica y confirma el sistema al asignar el tribunal evaluador o reemplazar a un miembro?

**Element:** UC-006  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

Los pasos 9 y 10, A1, A2 step 5, A3 y A5 notifican, confirman o informan, pero la especificación no fija los textos.

**Answer:** paso 9 y A2 step 5 (a cada miembro asignado): "Se le asignó como miembro del tribunal evaluador del
trámite de titulación de {estudiante}."; paso 9 (al estudiante): "Su trámite de titulación pasó a la fase
pre-defensa. Su tribunal evaluador: {m1}, {m2} y {m3}."; A2 step 5 (al miembro reemplazado): "Dejó de ser miembro
del tribunal evaluador del trámite de titulación de {estudiante}."; A2 step 5 (al estudiante): "Su tribunal
evaluador tiene un nuevo miembro: {nuevo}, en lugar de {anterior}."; paso 10: "Tribunal evaluador asignado: {m1},
{m2} y {m3}. Fase: pre-defensa."; A1: "El tribunal evaluador se compone de exactamente tres docentes distintos.";
A3: "El tribunal evaluador ya no se puede cambiar porque la defensa formal se realizó."; A5: "{docente} tiene la
{acto} de {estudiante} el {fecha y hora}, que se cruza con la {acto} de este trámite. Reprograme antes el acto o
elija otro docente."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-036: ¿Con qué textos confirma e informa el sistema al gestionar cuentas de usuario?

**Element:** UC-018  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El paso 8, A3, A5 y A7 confirman o informan, pero la especificación no fija los textos.

**Answer:** paso 8: "Cuenta registrada.", "Cuenta actualizada.", "Cuenta desactivada." o "Cuenta reactivada."; A3:
"No se puede desactivar la cuenta ni quitarle el rol docente. El director de carrera debe reasignar antes:" seguido
de la lista de trámites de titulación y papeles; A5: "El correo electrónico ya pertenece a otro usuario."; A7: "Ya
hay un director de carrera activo: {nombre}."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-037: ¿Qué pasa al reactivar a un exdirector de carrera mientras hay otro director de carrera activo?

**Element:** UC-018 A4  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

BR-002 admite a lo sumo un director de carrera activo, pero A4 no considera una cuenta inactiva que conserva el rol
director de carrera. Lecturas: (a) no se reactiva y se informa como en A7; (b) se reactiva quitándole el rol
director de carrera.

**Answer:** (a) no se reactiva y se informa "Ya hay un director de carrera activo: {nombre}."; el administrador del
sistema quita antes el rol (A1).  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-038: ¿Qué pasa con el registro de docente al quitar el rol docente y al volver a darlo?

**Element:** UC-018 A1  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El paso 7 registra al docente activo al dar el rol docente, pero A1 no dice qué pasa con ese registro al quitar el rol
(cuando BR-003 lo permite) ni al volver a darlo. Lecturas: (a) queda inactivo y se reutiliza; (b) se borra.

**Answer:** (a) al quitar el rol docente, el docente queda inactivo y no se borra; al volver a darlo, el mismo
registro vuelve a activo.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-039: ¿Qué formato tiene un teléfono válido?

**Element:** Entity: STUDENT.phone  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

UC-001 A8 rechaza un teléfono sin formato válido y el modelo lo marca `Format: Phone` sin fijar el formato. Lecturas:
(a) 7 u 8 dígitos con prefijo +591 opcional; (b) de 7 a 15 dígitos con + opcional.

**Answer:** (a) 7 dígitos (fijo) u 8 dígitos (celular), con el prefijo +591 opcional; el teléfono sigue siendo
opcional.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-040: ¿Cómo se crea la primera cuenta del administrador del sistema?

**Element:** UC-018  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

La precondición de UC-018 exige un administrador del sistema con sesión iniciada, pero ningún caso de uso crea esa
cuenta. Lecturas: (a) un seeder con los datos del archivo de entorno; (b) un comando de consola.

**Answer:** (a) un seeder toma el nombre, el correo electrónico y la contraseña del archivo de entorno; la cuenta
debe cambiar su contraseña inicial en el primer inicio de sesión.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-041: ¿Un miembro reemplazado del tribunal puede quedar como tutor del mismo trámite de titulación?

**Element:** UC-002 BR-001  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

BR-001 excluye al docente que "es miembro del tribunal de ese trámite", en presente, mientras UC-006 BR-001 habla de
"no es ni fue miembro". Lecturas: (a) solo se excluye a los miembros vigentes; (b) también a los reemplazados.

**Answer:** (a) solo se excluye a los miembros vigentes del tribunal.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-042: ¿Qué reglas cumple la nueva contraseña que reemplaza a la contraseña inicial?

**Element:** UC-018 BR-005  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

UC-018 BR-005 y UC-001 BR-005 obligan a cambiar la contraseña inicial en el primer inicio de sesión, pero no fijan
reglas para la nueva. Lecturas: (a) al menos 8 caracteres, confirmada y distinta de la inicial; (b) solo al menos 8
caracteres y confirmada.

**Answer:** (a) al menos 8 caracteres, confirmada y distinta de la contraseña inicial; vale también para el
estudiante (UC-001 BR-005).  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-043: ¿Con qué textos rechaza el sistema un tutor no elegible y pide confirmar al estudiante registrado?

**Element:** UC-001  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

BR-006 limita el tutor a los docentes elegibles y A2 pide que el asistente administrativo confirme que es el
estudiante que se presenta, pero la especificación no da los textos cuando falla una de esas verificaciones.

**Answer:** tutor no elegible: "El tutor elegido no es un docente elegible."; A2 sin confirmar: "Confirme que es el
estudiante que se presenta."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-044: ¿Con qué textos rechaza el sistema un docente no elegible como tutor o un trámite que no está en curso?

**Element:** UC-002  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

BR-001 y BR-002 limitan a quién y en qué trámite se asigna el tutor, pero la especificación no da los textos del
rechazo ni el de un trámite sin docentes elegibles.

**Answer:** "El docente elegido no es elegible como tutor de este trámite de titulación."; "Solo se asigna tutor en
un trámite de titulación en curso."; lista vacía: "No hay docentes elegibles como tutor de este trámite de
titulación."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-045: ¿Con qué textos rechaza el sistema la designación de un docente que no es elegible?

**Element:** UC-003 BR-002  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

BR-002 limita quién puede ser designado docente de taller de grado, pero la especificación no da los textos del
rechazo.

**Answer:** sin docente elegido: "Elija al docente de taller de grado."; docente no elegible: "El docente elegido no
puede ser designado como docente de taller de grado."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-046: ¿Con qué textos rechaza el sistema un miembro no elegible o un trámite que no recibe tribunal?

**Element:** UC-006  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

BR-001, BR-003 y BR-004 limitan a quién, en qué trámite y a qué miembro se asigna o reemplaza, pero la
especificación no da los textos del rechazo.

**Answer:** "Alguno de los docentes elegidos no es elegible como miembro del tribunal evaluador."; "El docente
elegido no es elegible como miembro del tribunal evaluador."; "El tribunal evaluador solo se asigna a un trámite de
titulación en curso, en la fase evaluación de perfil y con su perfil de proyecto aprobado."; "El tribunal evaluador
solo se reemplaza en un trámite de titulación en curso que ya lo tiene."; "El miembro que se reemplaza no es un
miembro vigente del tribunal evaluador."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-047: ¿Se informan todos los choques de horario del nuevo docente o solo el primero?

**Element:** UC-006 A5  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El nuevo miembro del tribunal (UC-006 A5), como el nuevo tutor (UC-002 A4), puede cruzarse con varios actos.
Lecturas: (a) un mensaje por cada acto que se cruza; (b) solo el primero.

**Answer:** (a) se informa cada acto que se cruza; vale igual para UC-002 A4.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-048: ¿Qué formato tienen el número de cédula de identidad y su complemento?

**Element:** Entity: STUDENT.id_card_number  
**Raised by:** /use-case-coverage, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El modelo fija solo las longitudes (20 y 5). Lecturas: (a) solo dígitos; (b) sin formato; (c) letras, dígitos y
guiones.

**Answer:** el número tiene solo dígitos; el complemento admite letras y dígitos.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-049: ¿Se guardan los demás cambios de una cuenta cuando se rechaza el retiro del rol docente?

**Element:** UC-018 A3  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

Al cambiar una cuenta (A1) quitándole el rol docente con papeles vigentes, A3 rechaza el retiro, pero no dice qué
pasa con el nombre o el correo cambiados en la misma confirmación.

**Answer:** se rechaza todo el cambio; el administrador del sistema lo repite sin quitar el rol docente.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-050: ¿El sistema muestra cuántos perfiles de proyecto recibirá el docente antes de confirmar la designación?

**Element:** UC-003 step 2  
**Raised by:** /use-case-coverage, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El paso 8 da la cantidad de perfiles que pasaron al nuevo revisor; la implementación la muestra también al elegir y
confirmar, cosa que los pasos 2 a 4 no piden.

**Answer:** sí, se muestra la cantidad de perfiles de proyecto en revisión que pasarán al docente elegido antes de
confirmar.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-051: ¿Se guarda el nombre del tutor propuesto cuando el trámite de titulación queda con tutor?

**Element:** UC-001 A5  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

La postcondición guarda el nombre del tutor propuesto en un trámite sin tutor, pero no dice qué pasa con ese nombre
cuando el asistente administrativo elige un tutor elegible.

**Answer:** se descarta; solo se guarda en un trámite que queda sin tutor.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-052: ¿El teléfono se guarda tal como se escribe o normalizado?

**Element:** Entity: STUDENT.phone  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

OQ-039 acepta el prefijo +591 opcional, pero no dice si se guarda.

**Answer:** se guarda normalizado, solo con los 7 u 8 dígitos, sin el prefijo +591.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-053: ¿La cuenta del administrador del sistema se lista y gestiona junto con las demás?

**Element:** UC-018 step 2  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El paso 2 lista las cuentas de docentes, del director de carrera y de asistentes administrativos, pero no dice qué
pasa con una cuenta de administrador del sistema que tenga además uno de esos roles.

**Answer:** no; una cuenta con el rol administrador del sistema no se lista ni se gestiona en UC-018.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-054: ¿Con qué textos notifica, confirma e informa el sistema al presentar una versión del proyecto?

**Element:** UC-004  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El paso 7, el paso 8, A1, A2, A3 y A4 notifican, confirman o informan, pero la especificación no fija los textos.

**Answer:** paso 7 (al docente de taller de grado): "{estudiante} presentó el perfil de proyecto (versión {n}), que
espera su revisión."; paso 7 (al tutor y a los miembros del tribunal, pre-defensa): "{estudiante} presentó la versión
{n} del proyecto de grado, que espera su revisión."; A2 (al tutor y a los miembros del tribunal): "{estudiante}
presentó la versión {n} del proyecto de grado para la defensa formal."; paso 8: "Versión {n} presentada. Estado:
{estado}."; A1: "Su perfil de proyecto queda pendiente de revisión hasta que el director de carrera designe al
docente de taller de grado."; A3: "No tiene un trámite de titulación en curso.", "Su trámite de titulación espera la
asignación de su tutor.", "Su versión {n} del proyecto sigue en revisión.", "Su perfil de proyecto ya fue aprobado y
espera la asignación del tribunal evaluador.", "Su versión de la fase pre-defensa ya fue aprobada y espera el
siguiente paso.", "Su defensa formal ya se realizó.", "Su pre-defensa no dejó observaciones que subsanar."; A4:
"Ingrese el título del proyecto de grado.", "Adjunte el archivo de la versión.", "El archivo debe ser PDF.", "El
archivo no debe superar los 20 MB."; título de más de 300 caracteres: "El título del proyecto de grado no debe superar
los 300 caracteres."; subida fallida: "No se pudo subir el archivo. Inténtelo de nuevo."; paso 2, última versión sin
observaciones: "Esta versión no tiene observaciones."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-055: ¿Con qué textos notifica, confirma e informa el sistema al evaluar un perfil de proyecto?

**Element:** UC-005  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

El paso 8, el paso 9, A1 step 4, A2, A3, BR-001 y BR-003 notifican, confirman o informan, pero la especificación no
fija los textos.

**Answer:** paso 8 (al estudiante): "Su perfil de proyecto (versión {n}) fue aprobado."; A1 step 4 (al estudiante):
"Su perfil de proyecto (versión {n}) fue rechazado. Puede presentar uno nuevo con las observaciones subsanadas.";
paso 9: "Perfil de proyecto aprobado." o "Perfil de proyecto rechazado."; A2: "El rechazo necesita al menos una
observación."; A3: "No hay perfiles de proyecto que esperen su revisión."; BR-001: "Solo el revisor del perfil de
proyecto puede evaluarlo."; BR-003: "El perfil de proyecto ya fue evaluado."; BR-004: "El trámite de titulación ya
tiene un perfil de proyecto aprobado."; sin decisión: "Elija si aprueba o rechaza el perfil de proyecto.";
observación de más de 2000 caracteres: "Cada observación admite como máximo 2000 caracteres."  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-056: ¿Quién ve la opción de menú para evaluar perfiles de proyecto?

**Element:** UC-005 BR-001  
**Raised by:** /use-case-implement, 2026-11-26  
**Blocking:** No  
**Status:** Answered  

Solo el docente de taller de grado evalúa perfiles de proyecto, pero los permisos se asignan por rol (ADR-005) y el
rol docente lo tienen todos los docentes. Lecturas: (a) todo docente ve la opción y la lista muestra solo los
perfiles de los que es revisor; (b) solo el docente de taller de grado ve la opción.

**Answer:** (b) solo el docente de taller de grado ve la opción "Perfiles de proyecto"; el permiso sigue en el rol
docente y la evaluación exige ser el revisor del perfil de proyecto.  
**Answered by:** Roger Roca Arano, 2026-11-26  

## OQ-057: ¿Qué observaciones ve el estudiante al presentar una versión en la fase defensa formal?

**Element:** UC-004 step 2  
**Raised by:** /use-case-coverage, 2026-11-26  
**Blocking:** No  
**Status:** Open  
**Assumed:** Solo las observaciones de su última versión del proyecto, como dice el paso 2.  

En la fase defensa formal el estudiante presenta versiones porque la pre-defensa dejó observaciones (BR-006), pero
esas observaciones son de la pre-defensa (OBSERVATION con `defense_id`), no de una versión del proyecto. El paso 2
muestra solo las observaciones de la última versión, que suele ser la versión aprobada de la fase pre-defensa, sin
observaciones: el estudiante ve "Esta versión no tiene observaciones." justo cuando tiene observaciones que subsanar.
Lecturas: (a) el paso 2 muestra además, en la fase defensa formal, las observaciones de la pre-defensa; (b) se deja
como está y el estudiante las consulta en otro caso de uso (UC-013).
