# Seguimiento de Trámites de Titulación

Aplicación web para el seguimiento del trámite de titulación por **Proyecto de Grado** de la carrera de Ingeniería en
Redes y Telecomunicaciones.

Hoy el trámite se gestiona en papel: la información de cada estudiante está repartida entre carpetas físicas, cartas y
versiones impresas del documento, y los avisos a estudiantes y docentes dependen del asistente administrativo. El
sistema permite que estudiantes, docentes (tutor, revisor, tribunal evaluador), el director de carrera y el asistente
administrativo sigan cada fase del trámite —requisitos, evaluación de perfil, pre-defensa, defensa formal y archivo— y
tengan un canal continuo para la revisión de la documentación del proyecto.

**Stack:** Laravel 13 · PHP 8.5 · Inertia v3 · React 19 · Tailwind CSS · Fortify · Wayfinder · Pest.

## Documentación

La especificación vive en [`docs/`](docs) y se mantiene junto con el código.

| Documento                                                 | Contenido                                                               |
|-----------------------------------------------------------|-------------------------------------------------------------------------|
| [Visión](docs/vision.md)                                  | Problema, flujo manual del trámite y solución propuesta                 |
| [Lineamientos técnicos](docs/lineamientos_tecnicos.md)    | Arquitectura y requisitos técnicos establecidos para el proyecto        |
| [Requisitos](docs/requirements.md)                        | Catálogo de requisitos funcionales, no funcionales y restricciones      |
| [Glosario](docs/glossary.md)                              | Términos del dominio y los que se deben evitar                          |
| [Modelo de entidades](docs/entity_model.md)               | Diagrama ER y tablas de atributos, tipos y validaciones                 |
| [Preguntas abiertas](docs/open_questions.md)              | Dudas sobre la especificación, su estado y dónde se resolvieron         |

Las reglas de trabajo para agentes de IA (git, convenciones de Laravel, pruebas) están en [`AGENTS.md`](AGENTS.md).

## Proceso de desarrollo con AI Unified Process (AIUP)

El proyecto sigue [AI Unified Process](https://unifiedprocess.ai), un flujo *requirements-first* que pasa de la
visión a especificaciones revisadas, implementación y pruebas trazables. Cada paso lo ejecuta un *skill* del agente
de IA (Claude Code), lee los artefactos del paso anterior y produce un documento en texto plano dentro de `docs/` que
se revisa antes de continuar.

```text
Inception        Elaboration                                   Construction                          Transition
─────────────    ──────────────────────────────────────────    ──────────────────────────────────    ──────────────────────
/requirements →  /entity-model → /stack → /use-case-diagram →  /use-case-spec → implementación →     /acceptance-protocol
                                                               pruebas → /use-case-coverage          /user-guide
                                                                                                     /release-notes
                 /spec-review en cualquier momento · /next indica el siguiente paso
```

1. **Inception** — `/requirements` deriva el catálogo de requisitos y el glosario de [`vision.md`](docs/vision.md) y
   los lineamientos técnicos.
2. **Elaboration** — `/entity-model` define el modelo de datos; `/use-case-diagram` identifica actores y casos de uso
   (`UC-XXX`), trazados a sus requisitos (`FR-XXX`).
3. **Construction** — por cada caso de uso: `/use-case-spec` escribe la especificación, se implementa y se prueba
   (Pest), y `/use-case-coverage` audita que cada paso, flujo alternativo y regla de negocio quede implementado y
   probado. `/test-case` describe los recorridos de prueba.
4. **Transition** — `/acceptance-protocol`, `/user-guide` y `/release-notes` entregan el sistema.

Comandos de apoyo:

- `/next` — muestra el estado del proyecto y el siguiente comando a ejecutar.
- `/spec-review` — revisa la coherencia de la especificación.
- `/open-questions` — registra y responde dudas en [`open_questions.md`](docs/open_questions.md) sin bloquear el avance.

Como AIUP no tiene plugins de construcción para Laravel, solo se usa `aiup-core`; la implementación sigue las
convenciones de Laravel con el apoyo de [Laravel Boost](#laravel-boost).

### Flujo de ramas

Cada fase o caso de uso se trabaja en su propia rama creada desde `develop`, con el nombre del desarrollador como
prefijo para saber quién hizo cada trabajo (por ejemplo `roger/UC-001-gestion-de-usuarios`, `javier/glosario` o
`alejandro/build`), y se integra de vuelta en `develop` mediante un pull request; `main` solo recibe lo entregado.
Los commits siguen Conventional Commits con el caso de uso o el área como *scope*, por ejemplo
`feat(UC-001): agrega registro de estudiante` o `docs(entity-model): agrega modelo de entidades`. Las reglas completas
están en [`.ai/guidelines/git.md`](.ai/guidelines/git.md).

### Instalar AIUP

Recomendado: tener [Claude Code](https://claude.com/claude-code) instalado. Dentro de Claude Code, en la raíz del
proyecto:

1. Agrega el marketplace (una sola vez):

   ```text
   /plugin marketplace add ai-unified-process/marketplace
   ```

2. Instala el plugin principal:

   ```text
   /plugin install aiup-core
   ```

3. Verifica la instalación ejecutando `/next`, que debe mostrar el estado actual del proyecto.

Para otros agentes (Codex, Cursor, Copilot, Gemini CLI…) los skills se pueden instalar con
[`npx skills`](https://skills.sh):

```bash
npx skills add AI-Unified-Process/marketplace/aiup-core
```

Más detalles en la [guía de instalación de AIUP](https://github.com/ai-unified-process/marketplace#installation).

## Laravel Boost

[Laravel Boost](https://github.com/laravel/boost) da al agente de IA contexto específico de esta aplicación:

- **Servidor MCP** (`php artisan boost:mcp`, configurado en [`.mcp.json`](.mcp.json)) con herramientas para consultar
  el esquema y los datos de la base de datos, leer logs y errores del navegador, obtener la URL de la aplicación y
  buscar en la documentación de Laravel, Inertia, Pest y Tailwind según las versiones instaladas.
- **Guidelines**: reglas que se generan en [`AGENTS.md`](AGENTS.md) a partir de los paquetes instalados y de las
  guías propias del proyecto en [`.ai/guidelines/`](.ai/guidelines).
- **Skills**: guías por dominio (Laravel, Inertia + React, Fortify, Wayfinder, Tailwind, pruebas) en
  [`.agents/skills/`](.agents/skills).

Boost ya está incluido como dependencia de desarrollo y configurado en [`boost.json`](boost.json) para Claude Code y
Zed. Después de `composer install`:

```bash
# Primera configuración o para cambiar agentes, skills o el MCP
php artisan boost:install

# Regenerar guidelines y skills tras actualizar paquetes o editar .ai/guidelines
php artisan boost:update
```

## Instalación local

Requisitos: PHP 8.5, Composer, Node.js y [Laravel Herd](https://herd.laravel.com) (o un servidor equivalente).

```bash
composer run setup   # instala dependencias, crea .env, genera la clave, migra y compila el frontend
composer run dev     # entorno de desarrollo (servidor y Vite)
```

### Pruebas y calidad

```bash
php artisan test --compact   # pruebas (Pest)
composer run lint            # formato PHP (Pint)
composer run ci:check        # lint y tipos del frontend + pruebas
```
