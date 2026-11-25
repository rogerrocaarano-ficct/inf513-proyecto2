---
name: ea-conceptual-model-sync
description: "Synchronizes the UML conceptual data model in Enterprise Architect with the repository's entity relationship model (docs/entity_model.md). Use when the user asks to sync, update, refresh, or regenerate the conceptual model or the \"Diseño conceptual\" diagram in Enterprise Architect (EA), to bring EA up to date after changing the entity model, or to check whether EA and docs/entity_model.md still match. Requires the Enterprise Architect MCP server (mcp__Enterprise_Architect__* tools). Do not use to edit docs/entity_model.md itself (that is /entity-model) or to produce physical or logical database diagrams."
---

# Sync the EA conceptual model

`docs/entity_model.md` is the source of truth. The Enterprise Architect model only mirrors it as a UML conceptual
class diagram: classes, attributes without types, and named associations with multiplicities. Never change
`docs/entity_model.md` from this skill; if the ER model looks wrong, tell the user.

## Target in Enterprise Architect

- Package: `Diseño de datos` (path `/Model/Diseño de datos`).
- Diagram: `Diseño conceptual`, type `Class`, owned by that package.
- Find both by name (`find_packages_by_name`, `get_packages_information`); never rely on IDs remembered from an earlier
  session. If either is missing, ask the user before creating it.

## Mapping rules (ER → conceptual)

### Classes

- One class per entity section (`### ENTITY`), named in PascalCase from the entity name: `GRADUATION_PROCESS` →
  `GraduationProcess`, `MENU_ITEM` → `MenuItem`. Keep the English names; do not translate.
- `USERS` (the Laravel boundary named at the top of the document) becomes the class `User` with only its business
  attributes `name` and `email` (no `id`, `password`, `email_verified_at`, `two_factor_secret`).
- The class description is the entity's description paragraph, verbatim.
- A pure junction entity, whose only attributes are the two keys it joins (e.g. `ROLE_PERMISSION`), is **not** a class:
  it becomes one many-to-many association (`0..*` on both ends) named with its ER relationship label.
- A junction entity with attributes of its own (e.g. `USER_ROLE`, `VERSION_APPROVAL`) stays a class with one
  association to each side.

### Attributes

- Keep each attribute name exactly as in the table (snake_case), in table order.
- Leave out surrogate keys (`id`, `Primary Key, Sequence`) and every `Foreign Key` column; the foreign keys become
  associations. A natural primary key that carries business meaning (not a sequence and not a foreign key) stays.
- No data types, lengths, validation rules, initial values, or stereotypes. Always pass `"type": ""` when creating an
  attribute: EA fills an empty type with `int` otherwise.
- Do not change the attribute scope EA assigns.

### Associations

- One `Association` per relationship line of the Mermaid `erDiagram`. The left entity is the source end, the right
  entity the target end. The association name is the relationship label, verbatim (it is in Spanish in the ER model).
- Multiplicities from the crow's-foot markers: `||` → `1`, `|o` / `o|` → `0..1`, `o{` / `}o` → `0..*`,
  `|{` / `}|` → `1..*`.
- Tighten an upper bound only when an entity constraint fixes it (e.g. a process has exactly three committee members
  → `0..3` on `CommitteeMember`).
- Foreign keys to `USERS` are not drawn in the Mermaid diagram; add an association from `User` to that class for each
  one. Name it with a short Spanish verb that matches the column description (e.g. `uploaded_by` → `adjunta`,
  `author_id` → `registra`, a notification's `user_id` → `recibe`). Multiplicity on the `User` end: `1` for a
  `Not Null` key, `0..1` for an `Optional` key. On the other end: `0..1` when the key is `Unique` (one-to-one, e.g.
  `Student`), else `0..*`.
- No role names on association ends, no direction, no navigability, no stereotypes, no association classes.
- Do not add enumerations or notes for the entity constraints.

## Procedure

1. **Read the source.** Read `docs/entity_model.md` completely and build the target model with the rules above:
   classes with descriptions and attributes, and associations with names and multiplicities. If the user mentions a
   recent change, also read `git log -p -- docs/entity_model.md` to see what changed.
2. **Read EA.** Locate the package and diagram. List the package's owned elements, then call
   `get_elements_information` for every class to get its attributes and connectors.
3. **Diff.** Match classes by name, attributes by name inside their class, and associations by source class, target
   class, and name. Classify every difference as add, update (rename, description, multiplicity), or remove.
   - When an entity disappears and another appears with an overlapping purpose or attributes (e.g.
     `PAGE_VISIT_COUNTER` → `PAGE`), treat it as a rename: update the existing class and its attributes so it keeps
     its ID and diagram position.
   - Elements in the package that never came from the ER model (notes, other diagrams) are not yours; leave them.
4. **Confirm removals.** Show the user the list of changes. Deleting a class, attribute, or association from the EA
   model cannot be undone from here, so apply removals only after the user confirms. Additions and updates need no
   confirmation when the user asked to sync.
5. **Apply.**
   - Classes: `create_or_update_elements` (`type: "Class"`, `owningPackageID` of the package).
   - Attributes: `create_or_update_attributes` with `"type": ""`, `order: -1`.
   - Associations: `create_or_update_connectors` (`type: "Association"`). Updating an end requires its
     `relatedElementID`; to clear an end name, send `"name": ""`.
   - Removals: `delete_from_model`.
6. **Place new classes** with `place_elements_on_diagram` in free space next to the classes they relate to:
   width `210`, height about `55 + 16 × attributes`. Leave existing positions untouched; the user arranges the layout.
7. **Reload and verify.** Call `reload_diagrams` for the diagram (needed after connector changes), then re-read the
   changed classes with `get_elements_information` and check them against the target model. Take a diagram image
   only when the user asks to see it.
8. **Report** what was added, renamed, updated, and removed, plus anything you could not map, and whether new classes
   may need manual layout.
