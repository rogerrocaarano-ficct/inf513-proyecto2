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
