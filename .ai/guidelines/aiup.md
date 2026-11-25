# AI Unified Process (AIUP)

This project follows the AI Unified Process with the `aiup-core` plugin. The specification lives in `docs/` and is
the source of truth for product, domain, and architecture decisions.

## What to read before working

- The use case or test case you work on, under `docs/use_cases/` and `docs/test_cases/`.
- Only the `FR-*`, `NFR-*`, and `C-*` rows of `docs/requirements.md` that the use case links on its
  `**Requirements:**` line, never the whole catalog.
- `docs/entity_model.md` and `docs/glossary.md`.
- `docs/open_questions.md`, for questions that affect the element you work on.
- The accepted architecture decision records under `docs/architecture/decisions/`, when they exist.
- `docs/vision.md`, `docs/lineamientos_tecnicos.md`, and the whole `docs/requirements.md` only when you work on the
  requirements themselves.

## Workflow

- Run the phases in order, reviewing each artifact with the user before the next step:
  - Inception: `/requirements` (catalog and glossary from the vision and the technical guidelines).
  - Elaboration: `/entity-model`, `/use-case-diagram`, optionally `/business-process`.
  - Construction: `/use-case-spec UC-XXX`, implementation, tests, coverage audit; `/test-case` for test journeys.
  - Transition: `/acceptance-protocol`, `/user-guide`, `/release-notes`.
- Use `/next` to find out the state of the project and the next step, and `/spec-review` to check a specification.
- When requirements change, reconcile the glossary, entity model, use case diagram, and affected use cases.

## Agents other than Claude Code

- The AIUP skills are portable Agent Skills; install them with
  `npx skills add AI-Unified-Process/marketplace/aiup-core` or copy the folders of `aiup-core/skills/` into the
  location the agent reads (e.g. `.agents/skills/`, `.github/skills/`).
- Slash commands (`/requirements`, `/next`, …) are Claude Code routing. Where the agent has no `/command` routing,
  state the same intent in the prompt (e.g. "generate the requirements catalog from the vision") and follow the
  matching skill.
- Sub-agents are Claude Code only. Where the agent cannot run sub-agents, use
  `aiup-core/agents/uc-coverage-layered.md` as a checklist and run the coverage audit directly.
- The documents in `docs/` are the contract between steps, whatever agent produced them: keep their format and
  identifiers exactly as the skills define them.

## Rules

- Do not implement a use case before its `UC-*.md` specification exists and its `**Status:**` is `Approved`. At
  `Draft` or `Reviewed`, ask the user whether to proceed or to run `/spec-review UC-XXX` first.
- Never set a use case to `Approved` or `Done` yourself: the product owner approves, and acceptance sets `Done`.
  Move a finished use case forward to `Implemented` or `Tested` only when the code and the passing tests support it.
- Record a gap or contradiction in the specification with `/open-questions` instead of assuming an answer. Do not
  implement a use case that has a blocking open question without asking the user.
- Keep requirement (`FR-XXX`, `NFR-XXX`, `C-XXX`), use case (`UC-XXX`), test case (`TC-XXX`), and open question
  (`OQ-XXX`) identifiers stable; reference the use case ID in code, tests, branches, and commits.
- Use the glossary terms in specifications, code, and UI text; avoid the terms listed in its "Avoid" column.
- Write the specification documents in Spanish; keep the field values the skills read (Priority, Status) in English.
- Record a new architecture decision with `/architecture-decision`, linked to the requirement or constraint it serves.

## Construction in Laravel

- AIUP has no layer plugin for Laravel, so construction follows this project's Laravel conventions and skills (Laravel
  Boost guidelines, `laravel-best-practices`, `inertia-react-development`, `testing-best-practices`).
- Derive migrations, models, and factories from `docs/entity_model.md`, keeping its names, types, and validation rules.
- Cover the main success scenario, alternative flows, and business rules of the use case with Pest feature tests, and
  name each test after the step or rule it verifies.
- After implementing, run the affected tests and Pint, and audit the use case against its specification before
  suggesting its next status. Report anything that could not be verified.
