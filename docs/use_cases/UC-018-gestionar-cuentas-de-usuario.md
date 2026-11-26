# Use Case: Gestionar cuentas de usuario

## Overview

**Use Case ID:** UC-018  
**Use Case Name:** Gestionar cuentas de usuario  
**Primary Actor:** Administrador del sistema  
**Goal:** Registrar, cambiar, desactivar y reactivar las cuentas de los docentes, el director de carrera y los asistentes administrativos, con su rol, para que cada uno participe en los trámites de titulación con su propia cuenta y acceda solo a lo que su rol permite  
**Trigger:** El administrador del sistema solicita gestionar las cuentas de usuario, por ejemplo cuando llega un docente nuevo o uno deja la carrera  
**Status:** Reviewed  

**Requirements:** [FR-003, FR-027, FR-029, NFR-005, NFR-006, NFR-009, NFR-010, NFR-013, NFR-015](../requirements.md)  
**Layers:** UI

## Preconditions

- El administrador del sistema inició sesión con el rol administrador del sistema.

## Main Success Scenario

1. El administrador del sistema solicita gestionar las cuentas de usuario.
2. El sistema muestra las cuentas de docentes, del director de carrera y de asistentes administrativos, con su nombre, su correo electrónico, sus roles y si están activas.
3. El administrador del sistema solicita registrar una cuenta.
4. El administrador del sistema ingresa el nombre completo, el correo electrónico, el rol (docente, director de carrera o asistente administrativo) y la contraseña inicial.
5. El administrador del sistema confirma el registro.
6. El sistema verifica que los datos estén completos, que el correo electrónico no pertenezca a otro usuario y que la contraseña inicial cumpla las reglas de contraseña (BR-005).
7. El sistema crea la cuenta activa con su rol y su contraseña inicial, que el usuario debe cambiar en su primer inicio de sesión; para un docente o el director de carrera, lo registra además como docente activo.
8. El sistema muestra la confirmación y la lista de cuentas actualizada.

## Alternative Flows

### A1: Cambiar una cuenta

**Trigger:** El administrador del sistema elige cambiar una cuenta existente (step 3)  
**Flow:**

1. El sistema muestra el nombre completo, el correo electrónico y el rol de la cuenta.
2. El administrador del sistema cambia el nombre, el correo electrónico o el rol, y confirma.
3. El sistema verifica los datos como en el paso 6 y, si el cambio quita el rol docente, también como al desactivar (BR-003); si la verificación lo impide, el use case continúa en A3.
4. El sistema guarda los cambios; el usuario trabaja con su nuevo rol desde su siguiente acción.
5. Use case continues at step 8.

### A2: Desactivar una cuenta

**Trigger:** El administrador del sistema elige desactivar una cuenta activa (step 3)  
**Flow:**

1. El sistema verifica que el usuario no sea tutor ni miembro vigente del tribunal de un trámite de titulación en curso, ni el docente de taller de grado (BR-003).
2. El administrador del sistema confirma la desactivación.
3. El sistema establece `USER.is_active` en falso, por lo que la cuenta ya no puede iniciar sesión, y, si es docente, lo registra como docente inactivo.
4. Use case continues at step 8.

### A3: Desactivación o retiro del rol docente bloqueado

**Trigger:** La verificación de A2.1, o la de A1.3 cuando el cambio quita el rol docente, determina que el usuario es tutor o miembro vigente del tribunal de un trámite de titulación en curso, o el docente de taller de grado  
**Flow:**

1. El sistema informa que la cuenta no se puede desactivar ni perder el rol docente, y lista los trámites de titulación y los papeles que el director de carrera debe reasignar antes (UC-002, UC-003, UC-006).
2. Use case ends.

### A4: Reactivar una cuenta

**Trigger:** El administrador del sistema elige reactivar una cuenta inactiva (step 3)  
**Flow:**

1. El administrador del sistema confirma la reactivación.
2. El sistema establece `USER.is_active` en verdadero y, si es docente, lo registra como docente activo.
3. Use case continues at step 8.

### A5: Correo electrónico de otro usuario

**Trigger:** El correo electrónico ya pertenece a otro usuario (step 6)  
**Flow:**

1. El sistema señala el correo repetido con un mensaje en español y conserva los demás datos.
2. El administrador del sistema corrige el correo electrónico.
3. Use case continues at step 5.

### A6: Datos incompletos o contraseña inválida

**Trigger:** Falta un dato, el correo electrónico no tiene un formato válido o la contraseña inicial no cumple las reglas de contraseña (step 6)  
**Flow:**

1. El sistema señala cada dato faltante o inválido con un mensaje en español y conserva lo ingresado, salvo la contraseña.
2. El administrador del sistema completa o corrige los datos.
3. Use case continues at step 5.

### A7: Ya hay un director de carrera

**Trigger:** El administrador del sistema asigna el rol director de carrera y ya hay otra cuenta activa con ese rol (step 6)  
**Flow:**

1. El sistema informa que ya hay un director de carrera activo y quién es.
2. El administrador del sistema elige otro rol, o desiste para quitar primero el rol al director de carrera actual.
3. Use case continues at step 5.

## Postconditions

### Success Postconditions

- Una cuenta registrada existe activa, con su rol, su contraseña inicial que debe cambiar en el primer inicio de sesión y, para un docente o el director de carrera, su registro como docente activo.
- Una cuenta cambiada tiene el nombre, el correo electrónico y el rol nuevos.
- Una cuenta desactivada no puede iniciar sesión y, si es docente, figura como inactivo; una reactivada vuelve a estar activa.
- Cada usuario ve en el menú solo las opciones que los permisos de su rol habilitan (FR-029).

### Failure Postconditions

- Ninguna cuenta queda con un correo electrónico de otro usuario ni sin rol.
- Ningún trámite de titulación en curso queda con un tutor o un miembro del tribunal inactivo, ni la carrera sin su docente de taller de grado.
- Ninguna cuenta se borra.

## Business Rules

### BR-001: Roles que asigna el administrador

El administrador del sistema asigna a cada cuenta uno de los roles que el sistema define: docente, director de carrera o asistente administrativo. Los roles y sus permisos son fijos (FR-027). Las cuentas de estudiante se crean al iniciar su trámite de titulación (UC-001).

### BR-002: El director de carrera es docente

El director de carrera trabaja también como docente: puede ser tutor, pero no miembro de un tribunal evaluador (UC-006 BR-001). Al cambiar al director de carrera, el usuario no deja de ser docente y conserva los trámites de titulación a los que está asignado como tutor. Hay a lo sumo un director de carrera activo.

### BR-003: Desactivación y retiro del rol docente

Una cuenta no se desactiva ni pierde el rol docente mientras su usuario sea tutor o miembro vigente del tribunal de un trámite de titulación en curso, o el docente de taller de grado (UC-003 BR-001). El director de carrera reasigna esos papeles antes. Si se intenta, el sistema lo impide (A3).

### BR-004: Cuentas permanentes

Una cuenta no se borra, para conservar la autoría de las observaciones, las calificaciones y la bitácora; se desactiva.

### BR-005: Contraseña inicial

El administrador del sistema escribe la contraseña inicial, de al menos 8 caracteres, y se la comunica al usuario fuera del sistema (C-005). El usuario debe cambiarla en su primer inicio de sesión, antes de usar cualquier otra función.

### BR-006: Menú según el rol

El menú de cada usuario se arma con las opciones que habilitan los permisos de su rol activo; un cambio de rol cambia el menú desde la siguiente página que abre (FR-029).
