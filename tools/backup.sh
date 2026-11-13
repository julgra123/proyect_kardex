#!/bin/sh
# Respaldo consistente y cifrado de Aula Kardex para Ubuntu Server.
# Requiere credenciales MariaDB y una frase secreta protegidas fuera del webroot.
set -eu

BACKUP_DIR=${KARDEX_BACKUP_DIR:-/var/backups/kardex}
DB_NAME=${KARDEX_DB_NAME:-kardex_escolar}
DB_CNF=${KARDEX_DB_CNF:-/etc/kardex/backup.cnf}
PASSPHRASE_FILE=${KARDEX_BACKUP_PASSPHRASE_FILE:-/etc/kardex/backup.passphrase}
RETENTION_DAYS=${KARDEX_BACKUP_RETENTION_DAYS:-14}

case "$DB_NAME" in
  ''|*[!A-Za-z0-9_]* ) echo "Nombre de base de datos no válido." >&2; exit 2 ;;
esac
case "$RETENTION_DAYS" in
  ''|*[!0-9]* ) echo "Retención no válida." >&2; exit 2 ;;
esac
[ "$RETENTION_DAYS" -ge 1 ] || { echo "La retención debe ser de al menos un día." >&2; exit 2; }
[ -r "$DB_CNF" ] || { echo "No se puede leer el archivo de credenciales: $DB_CNF" >&2; exit 1; }
[ -r "$PASSPHRASE_FILE" ] || { echo "No se puede leer la frase secreta de cifrado: $PASSPHRASE_FILE" >&2; exit 1; }
command -v mysqldump >/dev/null 2>&1 || { echo "No está instalado mysqldump." >&2; exit 1; }
command -v gpg >/dev/null 2>&1 || { echo "No está instalado GnuPG (gpg)." >&2; exit 1; }
command -v sha256sum >/dev/null 2>&1 || { echo "No está instalado sha256sum." >&2; exit 1; }

umask 077
mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"
TMP_SQL=$(mktemp "$BACKUP_DIR/.kardex_XXXXXX.sql")
TMP_GPG=$(mktemp "$BACKUP_DIR/.kardex_XXXXXX.gpg")
cleanup() { rm -f -- "${TMP_SQL:-}" "${TMP_GPG:-}"; }
trap cleanup EXIT
trap 'exit 1' HUP INT TERM

STAMP=$(date +%Y%m%d_%H%M%S)
FINAL="$BACKUP_DIR/kardex_${STAMP}.sql.gpg"
HASH="$FINAL.sha256"

# --defaults-extra-file debe aparecer antes de las demás opciones de mysqldump.
if ! mysqldump --defaults-extra-file="$DB_CNF" --single-transaction --quick --triggers "$DB_NAME" > "$TMP_SQL"; then
  echo "Falló mysqldump; no se conservará un respaldo incompleto." >&2
  exit 1
fi
[ -s "$TMP_SQL" ] || { echo "El respaldo generado está vacío." >&2; exit 1; }

# Cifrado simétrico OpenPGP con AES-256. El SQL temporal se mantiene con modo 600
# dentro de un directorio 700 y se elimina al terminar.
if ! gpg --batch --yes --pinentry-mode loopback --passphrase-file "$PASSPHRASE_FILE" \
    --symmetric --cipher-algo AES256 --output "$TMP_GPG" "$TMP_SQL"; then
  echo "Falló el cifrado del respaldo." >&2
  exit 1
fi
[ -s "$TMP_GPG" ] || { echo "El archivo cifrado está vacío." >&2; exit 1; }

mv -- "$TMP_GPG" "$FINAL"
TMP_GPG=''
chmod 600 "$FINAL"
(cd "$BACKUP_DIR" && sha256sum "$(basename "$FINAL")" > "$(basename "$HASH")")
chmod 600 "$HASH"
(cd "$BACKUP_DIR" && sha256sum -c "$(basename "$HASH")" >/dev/null)

find "$BACKUP_DIR" -type f \( -name 'kardex_*.sql.gpg' -o -name 'kardex_*.sql.gpg.sha256' \) -mtime "+$RETENTION_DAYS" -delete
printf 'Respaldo cifrado y verificado: %s\n' "$FINAL"
