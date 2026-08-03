---
name: generate-commit-message
description: >-
  Generates commit messages following a fixed personal standard, in the
  format "type(scope) - imperative short description + bullets". Use
  whenever the user asks to "create a commit", "generate a commit message",
  "commit these changes", "write a commit message", or asks to
  review/fix a commit message according to this standard. Always ask for
  the message language before generating the commit; if the user doesn't
  specify one, default to pt_BR. Apply this skill even if the user just
  pastes a diff or a list of changed files without explicitly saying "use
  the standard", since this is the user's fixed personal standard for all
  commits.
---

# Generate Commit Message

Skill for generating commit messages following the user's personal standard, in a consistent, specific way, avoiding vague generalities.

## Step 0 — Language (always first)

Before generating any commit, confirm the language of the message:

- If the user has already indicated the language (in the current conversation or in previous requests this session), use it without asking again.
- If not specified, **ask** which language should be used (e.g. pt_BR, en_US, es_ES...).
- If the user doesn't answer or says "doesn't matter"/"default", use **pt_BR**.

Important: commit `type`s (`feat`, `fix`, `refactor`, etc.) and the `type(scope): description` structure **never change language** — they are always in English, since they're a technical convention. Only the short description and the body bullets are written in the chosen language.

## Step 1 — Understand the change

Before writing the commit, identify what actually changed:

- If the user pasted a `git diff`, a list of changed files, or described the change in free text, analyze the content to understand the real intent of the change (not just the file names).
- If there isn't enough information to know what changed (e.g. the user only said "create a commit" with no context at all), ask specifically what was changed, or request the diff/file list.
- Never invent features or details that don't exist in the described change.

## Step 2 — Mandatory structure

Every generated commit must follow this exact format:

```
type(scope): Short imperative description
- Detail 1
- Detail 2
- Detail 3
```

## Step 3 — Allowed commit types

Use **only** one of these types (always in English, lowercase):

| Type | When to use |
|---|---|
| `feat` | New feature |
| `fix` | Bug fix |
| `refactor` | Refactoring without behavior change |
| `perf` | Performance improvement |
| `docs` | Documentation changes |
| `test` | Creating or adjusting tests |
| `chore` | Internal tasks (build, config, deps) |
| `ci` | CI/CD changes |
| `style` | Formatting (no logical impact) |

If the change doesn't clearly fit any type, pick the closest one based on the predominant effect of the change — never leave the field generic or empty.

## Step 4 — Scope

- Must indicate **where** the change happened.
- Short, objective name, usually one word (e.g. `auth`, `api`, `deploy`, `pipeline`, `database`, `ui`).
- Infer the scope from the changed files/folders (e.g. changes in `src/auth/*` → scope `auth`).
- If there are changes across multiple areas without an obvious common scope, pick the predominant scope of the main change, or suggest splitting into multiple commits (see Step 7).

## Step 5 — Short description

- Imperative mood (a direct instruction/command), never gerund or past participle.
- Starts with a verb.
- First letter capitalized.
- No trailing period.

Examples (en_US):
- ✅ `Add JWT authentication`
- ❌ `Adding authentication`
- ❌ `Added JWT`

Examples (pt_BR), if the chosen language is Portuguese:
- ✅ `Adiciona autenticação JWT`
- ❌ `Adicionando autenticação`
- ❌ `Adicionado JWT`

## Step 6 — Commit body (bullets)

- Optional, but recommended whenever there's more than one relevant change.
- Between 1 and 5 items.
- Each item:
  - Starts with a present-tense verb.
  - Is objective and specific.
  - Explains the actual impact of the change (not just repeating the file name).

Examples:
```
- Create login endpoint
- Validate user credentials
- Update authentication middleware
```

## Step 7 — General rules (always apply)

- Never use emojis.
- Never mix languages in the description/bullets (keep everything in the chosen language, except the technical `type`s).
- Never use vague phrases like "adjustments", "general improvements", "various fixes".
- Always be specific about what changed.
- A commit must represent **a single intent**. If the described change mixes very different intents (e.g. a new feature + an unrelated bug fix), warn the user and suggest splitting into separate commits, proposing each message.
- Avoid unnecessarily long commits — if it goes over 5 bullets, consider summarizing or splitting.

## Step 8 — Validation before delivering

Before presenting the final commit, mentally check:

- [ ] Follows the `type(scope): Description` format
- [ ] The type is one of the allowed ones (Step 3)
- [ ] The description is imperative, capitalized, with no trailing period
- [ ] The scope is coherent with the changed files/areas
- [ ] The bullets (if any) are specific, not generic
- [ ] No emojis and no language mixing
- [ ] The commit represents a single intent

If any item fails, fix it before responding.

## Delivery format

At the end, present the ready commit message inside a code block (for easy copy/paste), without long explanations before or after — just a short line of context if needed.

## Full examples (en_US)

**Example 1**
```
feat(pipeline): Adiciona execução automatizada de deploy
- Configura workflow no CI
- Executa build da aplicação
- Publica imagem no registry
```

**Example 2**
```
fix(auth): Corrigir validação de token expirado
- Ajusta verificação de validade
- Retorna erro 401 corretamente
```

**Example 3**
```
refactor(database): Melhorar organização de migrations
- Divide as migrations por contexto
- Elimina duplicações
```