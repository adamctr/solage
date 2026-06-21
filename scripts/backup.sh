#!/usr/bin/env bash
#
# Automatic PostgreSQL backup for Solage.
# Defaults target the Dokploy stack; override COMPOSE_FILE / DB_SERVICE for the
# bare-VPS stack (see DEPLOY-DOKPLOY.md and DEPLOYMENT.md §7).
# Writes a timestamped, gzipped dump to backups/ and prunes old ones.
#
set -euo pipefail

# Move to the project root, whatever directory cron invokes us from.
cd "$(dirname "$0")/.."

# Overridable so one script serves both stacks:
#   Dokploy (default):  docker-compose.dokploy.yml / service "solage-db"
#   bare VPS:           COMPOSE_FILE=docker-compose.prod.yml DB_SERVICE=postgres
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.dokploy.yml}"
DB_SERVICE="${DB_SERVICE:-solage-db}"
BACKUP_DIR="backups"
RETENTION_DAYS=14            # how many days of dumps to keep

mkdir -p "$BACKUP_DIR"
out="$BACKUP_DIR/solage-$(date +%F-%H%M%S).sql.gz"
tmp="$out.tmp"
trap 'rm -f "$tmp"' EXIT     # never leave a partial dump under the final name

# pg_dump runs INSIDE the container: credentials come from its environment
# (.env / Dokploy), never from the command line. -T: no TTY (cron-friendly).
# --clean --if-exists: makes the restore replayable (drop then recreate).
docker compose -f "$COMPOSE_FILE" exec -T "$DB_SERVICE" \
  sh -c 'pg_dump --clean --if-exists -U "$POSTGRES_USER" "$POSTGRES_DB"' \
  | gzip > "$tmp"

mv "$tmp" "$out"             # atomic rename: "$out" exists only if the dump succeeded

# Rotation: delete dumps older than RETENTION_DAYS.
find "$BACKUP_DIR" -name 'solage-*.sql.gz' -mtime +"$RETENTION_DAYS" -delete

echo "[backup] OK: $out ($(du -h "$out" | cut -f1))"
