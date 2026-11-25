# Lineamientos técnicos

Requisitos técnicos y funcionales establecidos para el proyecto. Este documento es una fuente del catálogo de
requisitos ([requirements.md](requirements.md)), junto con la visión ([vision.md](vision.md)).

## 2. Arquitectura del Sistema (3 Capas)

```
┌────────────────────────────────────────────────────────┐
│               CAPA 1: PRESENTACIÓN (Cliente)           │
│    React SPA / Vite + Inertia o Axios (Consumo API)    │
│    - Temas Dinámicos (Niños,Jóvenes,Adultos, Día/Noche)│
│    - Accesibilidad (Contraste y Escalado tipográfico)  │
└───────────────────────────┬────────────────────────────┘
                            │ (JSON / HTTP Requests via API RESTful)
                            ▼
┌────────────────────────────────────────────────────────┐
│            CAPA 2: LÓGICA DE NEGOCIO (Servidor)        │
│    Laravel Backend (API RESTful)                       │
│    - Matriz de Control de Acceso (RBAC) & Middleware   │
│    - Validaciones de Entradas (Mensajes en Español)    │
│    - Bitácora de Auditoría (Logins, Fallos, Tráfico)   │
│    - Motor de Negocio, Estadísticas y Módulo de Pagos  │
└───────────────────────────┬────────────────────────────┘
                            │ (Consultas SQL Transaccionales)
                            ▼
┌────────────────────────────────────────────────────────┐
│             CAPA 3: PERSISTENCIA (Base de Datos)       │
│    PostgreSQL (db.tecnoweb.org.bo)                     │
│    - Tablas relacionales, Triggers y Contadores        │
└────────────────────────────────────────────────────────┘
```

## 3. Requisitos Técnicos y Funcionales

### 3.1 Elementos de Diseño y Navegación

- Interfaz intuitiva, barra de navegación dinámica basada en los permisos del usuario logueado, y maquetación
  coherente con los lineamientos presentados en clases teóricas.

### 3.2 Gestión de Accesos y Roles (RBAC)

- Al menos dos (2) roles de negocio diferenciados (excluyendo al rol Administrador del sistema).
- Menú dinámico renderizado desde la base de datos en función de los permisos concedidos al rol activo.

### 3.3 Stack Tecnológico Obligatorio (API RESTful)

- Backend: Laravel (exponiendo endpoints seguros bajo arquitectura RESTful).
- Frontend: React (consumiendo la API mediante cliente HTTP).

### 3.4 Control de Acceso y Bitácora de Auditoría

- Matriz de Acceso: Restricción de rutas y acciones por recursos según perfiles.
- Sistema de Bitácora (Logs): Registro persistente en base de datos de los inicios de sesión exitosos, intentos
  fallidos, direcciones IP y recursos con mayor índice de acceso.

### 3.5 Estilo Único y Adaptabilidad Temática (Temas y Accesibilidad)

- Identidad visual unificada en todo el sitio (CSS / Tailwind / Framework de diseño).
- Selector de al menos 3 temas dinámicos orientados a segmentos de usuarios (Niños, Jóvenes, Adultos) combinados con
  la opción de Modo Día/Noche (conmutación automática o manual según horario).
- Herramientas de accesibilidad integradas: Control deslizante o botones para cambiar el tamaño de las fuentes y
  alternar contrastes de color (WCAG compliance básico).

### 3.6 Validación Estricta de Entradas

- Validación robusta tanto en el cliente como en el servidor para todos los formularios de la aplicación.
- Mensajes de error orientados al usuario y redactados íntegramente en idioma Español.

### 3.7 Contador de Visitas Persistente

- Contador de accesos por página web, ubicado obligatoriamente en el pie de página (Footer) de cada vista del sitio.

### 3.8 Estadísticas del Negocio y Acceso

- Panel analítico (Dashboard) que muestre gráficos o métricas clave relacionadas con la actividad del negocio y el
  flujo de accesos del sitio web.

### 3.9 Búsqueda Global de Información

- Componente de búsqueda integrado de forma prominente en el encabezado (Header) de la página principal (ej. barra de
  búsqueda predictiva o formulario de consulta rápida).

## 4. Infraestructura y Despliegue en Servidores

- Servidor Web Institucional: `https://www.tecnoweb.org.bo/inf513/grupoXXsc/proyecto2`
- Base de Datos: PostgreSQL en `db.tecnoweb.org.bo` (esquema asignado al grupo).
- Control de Versiones: Repositorio en GitHub obligatorio
