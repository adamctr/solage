# CLAUDE.md

Behavioral guidelines to reduce common LLM coding mistakes. Merge with project-specific instructions as needed.

Tradeoff: These guidelines bias toward caution over speed. For trivial tasks, use judgment.

## 1. Think Before Coding
Don't assume. Don't hide confusion. Surface tradeoffs.

Before implementing:

- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them - don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

## 2. Simplicity First
Minimum code that solves the problem. Nothing speculative.

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes
Touch only what you must. Clean up only your own mess.

When editing existing code:

- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it - don't delete it.

When your changes create orphans:

- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

## 4. Goal-Driven Execution
Define success criteria. Loop until verified.

Transform tasks into verifiable goals:

- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Refactor X" → "Ensure tests pass before and after"

For multi-step tasks, state a brief plan:

```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.

These guidelines are working if: fewer unnecessary changes in diffs, fewer rewrites due to overcomplication, and clarifying questions come before implementation rather than after mistakes.

---

## Project context: this is a graded diploma project

The user will defend this codebase orally to a jury. Optimize accordingly:

- **Defensibility over cleverness.** Every architectural choice must have a one-line "why" the user can repeat. If a pattern can't be explained in 30 seconds, simplify it.
- **Few, well-justified pieces** beat many half-baked ones. A small clean codebase the user fully owns scores higher than a large one with magic they didn't write.
- **Avoid the "AI smell"**: speculative abstractions, defensive programming for impossible cases, three-layer factories for a one-line function. A jury will spot it and ask why.
- **When proposing a change, give the *jury answer*** — what would the user say if asked "why did you do it this way?" If you can't formulate that answer in one sentence, don't propose the change.
- **Mention tradeoffs explicitly.** "I chose X over Y because Z, accepting the cost of W." Senior thinking is visible in the tradeoffs you name, not the choices you make.
- **Surface what's worth surfacing.** When you spot something a jury will challenge (security gap, inconsistent pattern, dead code), call it out — don't paper over it silently.

---

## Project-specific: Solage

PHP MVC app, custom router. PostgreSQL via PDO. FrankenPHP + Caddy + Traefik in Docker.

### Layering — respect it

```
public/index.php  → entrypoint (autoload, session_start, router)
routes/           → URL → "Controller#method" mappings only
modules/
  controllers/    → request handling, calls models, picks views
  models/         → all SQL lives here; PDO prepared statements
  views/          → output (HTML / JSON); no business logic
  validators/     → domain input validation (returns result arrays, no HTTP output)
src/              → framework code (Router, Migrations, Middleware, Utils, Logger)
includes/         → bootstrap (Database, Autoloader)
```

**Hard rules:**

- **No SQL outside `modules/models/`.** Controllers must call model methods, not write queries. If a needed query doesn't exist, add a method to the relevant model first.
- **No `echo` / output outside `modules/views/`** (and entrypoint). Controllers return data or invoke views.
- **No `$_POST` / `$_GET` / `$_SESSION` outside controllers and `SessionManager`.** Models receive data via parameters.
- **No direct `Database::getConnection()` outside models, `Migrations`, and the bootstrap.** Controllers go through models.

### PostgreSQL conventions

- Schema is `solage.pg.sql` (loaded once via `/docker-entrypoint-initdb.d/`). Additive changes go through `src/Migrations.php` (idempotent, uses `information_schema`).
- `user` is reserved in PG → FK columns are named `user_id`. Don't reintroduce a bare `user` column.
- All queries use prepared statements with named or positional placeholders. Never interpolate user input into SQL.

### Docker

- Dev: `docker-compose.yml` → Traefik (HTTP), FrankenPHP (bind-mount `./`), Postgres (port 5432 exposed for tools).
- Prod: `docker-compose.prod.yml` → Traefik (HTTPS via Let's Encrypt), FrankenPHP (image, no mount), Postgres (no port).
- After editing Traefik labels, run `docker compose restart traefik` (the docker provider sometimes misses recreated containers).

### Secrets

- `.env` is gitignored. Credentials are loaded by `vlucas/phpdotenv` in `includes/database.php`.
- `.env.example` is the template — keep it in sync when adding env vars.
- Never commit real credentials, even temporarily.

### Git commits

- **Never add Claude (or any AI) as commit co-author.** No `Co-Authored-By: Claude ...` trailer, no mention of Claude / Anthropic in commit messages. The user owns this codebase for a graded defense — every commit must read as the user's own work.
