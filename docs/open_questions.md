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
**Status:** Open  
**Assumed:** WCAG 2.1 nivel AA: contraste de al menos 4,5:1 en todos los temas y de 7:1 en el modo de alto contraste.  

Los lineamientos técnicos (3.5) piden "WCAG compliance básico" sin nombrar versión ni nivel. Lecturas: (a) WCAG 2.1
nivel A; (b) WCAG 2.1 nivel AA, el nivel que suelen exigir las normas de accesibilidad.

## OQ-009: ¿Qué métricas muestra el panel de estadísticas de trámites?

**Element:** FR-036  
**Raised by:** /requirements, 2026-11-25  
**Blocking:** No  
**Status:** Open  
**Assumed:** Cantidad de trámites de titulación por fase, defensas programadas y calificación promedio.  

Los lineamientos técnicos (3.8) piden "métricas clave relacionadas con la actividad del negocio" sin nombrarlas.
Otras posibles: tiempo promedio por fase, perfiles de proyecto rechazados, observaciones por versión del proyecto.

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
