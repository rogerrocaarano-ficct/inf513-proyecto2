# Use Case: Iniciar trámite de titulación

## Overview

**Use Case ID:** UC-001  
**Use Case Name:** Iniciar trámite de titulación  
**Primary Actor:** Asistente administrativo  
**Goal:** Registrar el trámite de titulación de un estudiante, con su cuenta y su documentación de requisitos, para que el trámite se siga en el sistema y no en una carpeta física  
**Trigger:** Un estudiante se presenta en la dirección de carrera con su documentación de requisitos para iniciar un trámite de titulación  
**Status:** Reviewed  

**Requirements:** [FR-001, FR-002, FR-022, NFR-005, NFR-006, NFR-008, NFR-009, NFR-013, C-004, C-014, C-016](../requirements.md)  
**Layers:** UI

## Preconditions

- El asistente administrativo inició sesión y tiene el rol asistente administrativo.

## Main Success Scenario

1. El asistente administrativo solicita iniciar un trámite de titulación.
2. El sistema muestra el formulario de inicio del trámite de titulación con la lista de docentes elegibles como tutor (BR-006).
3. El asistente administrativo ingresa el número de registro universitario del estudiante.
4. El sistema verifica que el número de registro universitario no pertenece a un estudiante registrado y pide los datos del estudiante.
5. El asistente administrativo ingresa los datos del estudiante: nombre completo, correo electrónico, número de cédula de identidad con su complemento si lo tiene, y teléfono.
6. El asistente administrativo adjunta la boleta de inscripción, la hoja de vida y la carta de solicitud del estudiante.
7. El asistente administrativo elige como tutor, entre los docentes elegibles, al docente que el estudiante propone en su carta de solicitud.
8. El asistente administrativo confirma el inicio del trámite de titulación.
9. El sistema verifica que los datos del estudiante estén completos y no pertenezcan a otro usuario, y que cada documento cumpla el formato exigido.
10. El sistema crea la cuenta del estudiante con el rol estudiante y su contraseña inicial, salvo que el estudiante ya esté registrado (BR-007).
11. El sistema registra el trámite de titulación del estudiante con su documentación de requisitos y su tutor, en la fase que fija BR-006.
12. El sistema notifica al tutor, cuando el trámite de titulación lo tiene, que se le asignó el trámite.
13. El sistema muestra la confirmación del trámite de titulación iniciado y, para una cuenta nueva, indica al asistente administrativo que la contraseña inicial del estudiante es su número de cédula de identidad.

## Alternative Flows

### A1: El estudiante ya tiene un trámite de titulación en curso

**Trigger:** El número de registro universitario pertenece a un estudiante con un trámite de titulación en curso (step 4)  
**Flow:**

1. El sistema muestra el nombre completo del estudiante e informa que tiene un trámite de titulación en curso, con su fase actual.
2. Use case ends.

### A2: Estudiante que reprobó o abandonó un trámite de titulación anterior

**Trigger:** El número de registro universitario pertenece a un estudiante registrado sin trámite de titulación en curso ni aprobado (step 4)  
**Flow:**

1. El sistema muestra el nombre completo y la cédula de identidad del estudiante, y sus trámites de titulación anteriores, cada uno reprobado o abandonado.
2. El asistente administrativo confirma que es el estudiante que se presenta.
3. Use case continues at step 6.

### A3: El estudiante ya aprobó su trámite de titulación

**Trigger:** El número de registro universitario pertenece a un estudiante con un trámite de titulación aprobado (step 4)  
**Flow:**

1. El sistema muestra el nombre completo del estudiante e informa que ya aprobó su trámite de titulación.
2. Use case ends.

### A4: Número de registro universitario mal digitado

**Trigger:** El estudiante registrado cuyo nombre muestra el sistema al buscar el número de registro universitario no es el que se presenta (step 4)  
**Flow:**

1. El asistente administrativo corrige el número de registro universitario.
2. Use case continues at step 4.

### A5: El tutor propuesto no es elegible

**Trigger:** El docente que la carta de solicitud propone no figura entre los docentes elegibles como tutor (step 7)  
**Flow:**

1. El asistente administrativo deja el trámite de titulación sin tutor y anota el nombre del tutor propuesto tal como figura en la carta de solicitud.
2. Use case continues at step 8.

### A6: El estudiante no propone tutor

**Trigger:** La carta de solicitud no nombra a un tutor (step 7)  
**Flow:**

1. El asistente administrativo deja el trámite de titulación sin tutor.
2. Use case continues at step 8.

### A7: Datos que pertenecen a otro usuario

**Trigger:** El correo electrónico o la cédula de identidad ya pertenecen a otro usuario (step 9)  
**Flow:**

1. El sistema señala el dato repetido y conserva los demás datos y documentos ingresados.
2. El asistente administrativo corrige el dato.
3. Use case continues at step 8.

### A8: Datos incompletos o inválidos

**Trigger:** Falta un dato obligatorio del estudiante, o el correo electrónico o el teléfono no tienen un formato válido (step 9)  
**Flow:**

1. El sistema señala cada dato faltante o inválido con un mensaje en español y conserva lo ingresado.
2. El asistente administrativo completa o corrige los datos.
3. Use case continues at step 8.

### A9: Documento que no cumple el formato

**Trigger:** Falta uno de los tres documentos, o un documento no es PDF o supera los 20 MB (step 9)  
**Flow:**

1. El sistema señala el documento faltante o el que no cumple el formato, con el motivo.
2. El asistente administrativo adjunta el documento correcto.
3. Use case continues at step 8.

## Postconditions

### Success Postconditions

- Existe un trámite de titulación del estudiante con la fecha y hora de su registro, y la boleta de inscripción, la hoja de vida y la carta de solicitud adjuntas, cada una con el usuario que la adjuntó.
- Un estudiante nuevo tiene una cuenta con el rol estudiante, cuya contraseña inicial es su número de cédula de identidad y que debe cambiarla en su primer inicio de sesión; un estudiante ya registrado conserva su cuenta y su contraseña.
- Con tutor elegido, el trámite de titulación está en la fase evaluación de perfil, tiene tutor y el tutor tiene una notificación de la asignación.
- Sin tutor elegido, el trámite de titulación está en la fase requisitos, sin tutor, con el nombre del tutor propuesto cuando la carta de solicitud lo nombra.

### Failure Postconditions

- No se crea la cuenta del estudiante sin su trámite de titulación, ni un trámite de titulación sin sus tres documentos de requisitos.
- No se guarda ningún archivo de un trámite de titulación que no se registró.
- La cuenta y los trámites de titulación de un estudiante ya registrado no se modifican.

## Business Rules

### BR-001: Documentación de requisitos completa

Un trámite de titulación se inicia solo con sus propios tres documentos de requisitos: una boleta de inscripción, una hoja de vida y una carta de solicitud, un documento de cada tipo. Un nuevo trámite de titulación de un estudiante ya registrado no reutiliza los documentos de sus trámites anteriores.

### BR-002: Formato de los documentos

Cada documento de requisitos es un archivo PDF de hasta 20 MB (C-004). El archivo se guarda en el almacenamiento de archivos del servidor y no se borra mientras el trámite de titulación exista (C-014, NFR-008).

### BR-003: Un trámite de titulación en curso por estudiante

Un estudiante tiene a lo sumo un trámite de titulación en curso. Un trámite de titulación deja de estar en curso cuando concluye, al registrarse la calificación de su defensa formal (aprobado con 51 o más, reprobado con menos, C-016), o cuando queda abandonado porque el estudiante no se presentó a su pre-defensa o defensa formal (BR-008). El sistema no exige un plazo entre un trámite reprobado o abandonado y el siguiente.

### BR-004: Identificación única del estudiante

El correo electrónico identifica una sola cuenta de usuario; el número de registro universitario y la cédula de identidad, con su complemento, identifican a un solo estudiante.

### BR-005: Contraseña inicial

La contraseña inicial de un estudiante nuevo es su número de cédula de identidad, sin el complemento. El estudiante debe cambiarla en su primer inicio de sesión, antes de usar cualquier otra función del sistema.

### BR-006: Tutor al iniciar el trámite

Solo un docente activo que no es el docente de taller de grado puede quedar como tutor. Un trámite de titulación con tutor se inicia en la fase evaluación de perfil; uno sin tutor se inicia en la fase requisitos y queda a la espera de que el director de carrera le asigne uno (UC-002).

### BR-007: Estudiante ya registrado

Un nuevo trámite de titulación de un estudiante ya registrado usa su cuenta y sus datos existentes: el sistema no crea otra cuenta ni cambia su contraseña.

### BR-008: Trámite abandonado

Un trámite de titulación queda abandonado cuando un miembro del tribunal registra que el estudiante no se presentó a su pre-defensa o defensa formal (UC-010, UC-011). UC-001 no abandona trámites: solo inicia uno nuevo para un estudiante cuyos trámites anteriores están reprobados o abandonados.

### BR-009: Trámite aprobado

Un estudiante con un trámite de titulación aprobado ya obtuvo su título de licenciatura y no inicia otro trámite de titulación.
