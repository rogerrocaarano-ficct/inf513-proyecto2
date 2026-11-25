# Git

- Branches:
  - `main`: what was delivered. It only receives merges from `develop`.
  - `develop`: integration of the current work.
  - `UC-xxx-<name>` for a use case (e.g. `UC-001-gestion-de-usuarios`), or a
    short descriptive name for shared work (e.g. `glosario`, `build`), both
    created from `develop` and merged back into it.
- Never commit directly to `main` or `develop`.
- Commits follow Conventional Commits: `<type>(<scope>): <description>`.
  - Type: `feat`, `fix`, `docs`, `test`, `refactor`, `build`, `chore`.
  - Scope: the use case ID (`feat(UC-001): …`) or the area (`docs(glossary): …`).
  - Description: imperative, in Spanish, lowercase, no final period; first
    line at most 72 characters.
- One logical change per commit: do not mix two use cases.
- Before every commit, show the user the files and the message, and commit
  only after they confirm.
