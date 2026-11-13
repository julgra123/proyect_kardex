# Respaldos cifrados en Ubuntu Server

El script `tools/backup.sh` genera respaldos SQL cifrados con OpenPGP/GnuPG y
AES-256. Los archivos se guardan por defecto en `/var/backups/kardex`, fuera
del directorio web. El SQL temporal se crea con permisos restrictivos y se
elimina después del cifrado.

## 1. Preparar credenciales y clave de cifrado

Como administrador del servidor:

```bash
sudo install -d -o root -g root -m 700 /etc/kardex
sudo nano /etc/kardex/backup.cnf
```

Contenido del archivo de opciones MariaDB (usar una clave única):

```ini
[client]
user=kardex_backup
password=COLOCAR_UNA_CLAVE_LARGA_Y_UNICA
host=localhost
```

Protegerlo:

```bash
sudo chown root:root /etc/kardex/backup.cnf
sudo chmod 600 /etc/kardex/backup.cnf
```

Generar una frase secreta aleatoria para cifrar los respaldos:

```bash
sudo sh -c 'umask 077; openssl rand -base64 48 > /etc/kardex/backup.passphrase'
sudo chown root:root /etc/kardex/backup.passphrase
sudo chmod 600 /etc/kardex/backup.passphrase
```

La frase secreta no debe guardarse dentro del proyecto, en la base de datos ni
en el mismo medio que los respaldos. Conserva una copia segura fuera del servidor
(por ejemplo, en un gestor de secretos o medio offline protegido). Si se pierde,
los respaldos cifrados no podrán recuperarse.

## 2. Preparar directorio de respaldos

```bash
sudo install -d -o root -g root -m 700 /var/backups/kardex
```

El ejemplo utiliza cron de root para leer los archivos protegidos y escribir en
ese directorio. El usuario de MariaDB definido en `backup.cnf` debe tener solo `SELECT`, `SHOW VIEW`
y `TRIGGER` sobre la base; no usar `root` de MariaDB. El respaldo no incluye
rutinas ni eventos porque esta aplicación no los necesita.

## 3. Ejecutar una prueba

```bash
sudo KARDEX_DB_CNF=/etc/kardex/backup.cnf \
  KARDEX_BACKUP_PASSPHRASE_FILE=/etc/kardex/backup.passphrase \
  /var/www/html/kardex/tools/backup.sh
```

El resultado será un archivo `.sql.gpg` y su `.sha256`. El script verifica el
hash antes de informar éxito y conserva 14 días por defecto.

## 4. Programar el respaldo diario

Editar el cron de root con `sudo crontab -e` y añadir (ajustar la ruta):

```cron
0 2 * * * KARDEX_DB_CNF=/etc/kardex/backup.cnf KARDEX_BACKUP_PASSPHRASE_FILE=/etc/kardex/backup.passphrase /var/www/html/kardex/tools/backup.sh >> /var/log/kardex-backup.log 2>&1
```

## 5. Verificar y restaurar en un entorno de prueba

Primero comprobar integridad del archivo cifrado:

```bash
cd /var/backups/kardex
sha256sum -c kardex_FECHA_HORA.sql.gpg.sha256
```

Descifrar a un archivo temporal protegido (no a la carpeta web):

```bash
sudo install -d -o root -g root -m 700 /var/tmp/kardex-restore
sudo sh -c 'umask 077; gpg --batch --pinentry-mode loopback --passphrase-file /etc/kardex/backup.passphrase --decrypt /var/backups/kardex/kardex_FECHA_HORA.sql.gpg > /var/tmp/kardex-restore/restauracion.sql'
```

Crear una base de pruebas vacía y restaurar ahí, nunca encima de producción durante
una demostración:

```bash
sudo mysql -e 'CREATE DATABASE kardex_prueba_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
sudo mysql kardex_prueba_restore < /var/tmp/kardex-restore/restauracion.sql
sudo rm -f /var/tmp/kardex-restore/restauracion.sql
```

La prueba de recuperación debe verificar que las tablas y registros esperados estén
presentes y que la aplicación pueda consultar la base restaurada. Mantener además
una copia cifrada fuera de la máquina virtual para cubrir pérdida total del servidor.
