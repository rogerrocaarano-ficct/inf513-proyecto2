# Git

- Branches:
  - `main`: what was delivered. It only receives merges from `develop`.
  - `develop`: integration of the current work.
  - Work branches are prefixed with the developer's name in lowercase,
    `<developer>/<name>`, so each branch shows who did the work:
    `<developer>/UC-xxx-<name>` for a use case (e.g.
    `roger/UC-001-gestion-de-usuarios`), or `<developer>/<short-name>` for
    shared work (e.g. `javier/glosario`, `alejandro/build`). They are created
    from `develop` and merged back into it through a pull request.
  - Each developer works on their own branches; to continue someone else's
    work, branch from theirs under your own prefix.
- Never commit directly to `main` or `develop`.
- Commits follow Conventional Commits: `<type>(<scope>): <description>`.
  - Type: `feat`, `fix`, `docs`, `test`, `refactor`, `build`, `chore`.
  - Scope: the use case ID (`feat(UC-001): …`) or the area (`docs(glossary): …`).
  - Description: imperative, in Spanish, lowercase, no final period; first
    line at most 72 characters.
- One logical change per commit: do not mix two use cases.
- Before every commit, show the user the files and the message, and commit
  only after they confirm.
