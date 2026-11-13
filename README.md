# Aula Kardex — versión con controles de seguridad (Partes 1 a 6)

## Instalación (XAMPP)
1. Copia la carpeta `kardex` a `C:\xampp\htdocs\`.
2. En phpMyAdmin: base `kardex_escolar` → importa `kardex_escolar.sql` (si es instalación nueva), opcionalmente `datos_demo.sql`, y **siempre** `migracion_seguridad.sql`.
3. En una consola: `C:\xampp\php\php.exe C:\xampp\htdocs\kardex\tools\migrar_identidades.php`
   - Crea las identidades, imprime **una sola vez** las contraseñas temporales de los estudiantes y la clave de la cuenta de emergencia (guárdala impresa, fuera del sistema).
   - Si encuentra cuentas que conservan la contraseña inicial conocida `123456`, obliga a cambiarla en el siguiente ingreso.
   - Re-ejecútalo si vuelves a importar `datos_demo.sql`.
4. Entra a `http://localhost/kardex/login.php` con la cuenta de administración. Si la cuenta conserva la clave inicial `123456`, el sistema obligará a cambiarla. Luego activa **Mi seguridad (2FA)** (Google/Microsoft Authenticator).

> Esta copia requiere pruebas de ejecución en el entorno local/servidor. La revisión actual verificó sintaxis PHP y del script de respaldo; todavía no se ha ejecutado una prueba funcional contra MariaDB.

## Dónde se cumple cada parte
| Parte | Qué se implementó | Dónde verlo / probarlo |
|---|---|---|
| 1a-b Identidades únicas con estado | Tabla `identidades` (usuario único, estado activa/suspendida/revocada) | `identidades.php` |
| 1c Altas, bajas, cambios de función | Altas al crear persona; «Dar de baja» = revocar (no borra notas); `cambios_rol` | `identidad.php`, `auditoria.php` |
| 1d Identidad desactivada no opera | Cada petición revalida estado y sesión en BD; suspender/revocar cierra sesiones | Suspende una cuenta con sesión abierta y recarga su pestaña |
| 1e Cambio de rol autorizado y auditado | Solo admin, con motivo + código 2FA, no sobre sí mismo, dentro de su familia | `identidad.php` → «Cambiar de rol» |
| 2b Hash de contraseñas | Argon2id (64 MiB, t=3) con rehash automático; bcrypt costo 12 si el PHP no lo trae | `includes/seguridad.php` |
| 2c Intentos repetidos | 5 fallos/15 min por usuario (y 20 por IP) → bloqueo; aplica igual a usuarios inexistentes | Falla 5 veces el login |
| 2d Expiración/revocación de sesión | Sesiones en BD: 15 min de inactividad, 8 h máximas, revocables; ID regenerado al ingresar | `identidad.php`, `configuracion.php` |
| 2e Segundo factor | TOTP (RFC 6238) exigido a administradores y docentes al crear/modificar calificaciones; también a administración en operaciones de alto riesgo | `mfa_config.php` |
| 2f Mensajes que no revelan cuentas | Un único mensaje para clave errónea, cuenta inexistente, suspendida o revocada | `login.php` |
| 3 Autorización en servidor | `autorizar(operación, recurso)`: identidad + operación + recurso + permisos + contexto (docente→materia y curso asignados; estudiante→su propio kardex) | `matriz.php`, prueba cambiar `?re=` o enviar una nota a un curso no asignado |
| 4 Auditoría | Login ok/fallido/bloqueado, roles, notas, denegaciones, emergencia, configuración; cadena de hashes | `auditoria.php`, `integridad.php` |
| 5 Recuperación | Modo solo-lectura/bloqueado, cuenta de emergencia, historial y sello por nota, restauración, backups | `configuracion.php`, `integridad.php`, `docs/PARTE5_recuperacion.md` |
| 6 Matriz de mínimo privilegio | Tabla `permisos` visible y aplicada | `matriz.php` |

## Otros controles incluidos
CSRF en todos los formularios POST, cabeceras de seguridad (CSP, X-Frame-Options, nosniff), cookie de sesión HttpOnly/SameSite=Strict, contraseñas con política (10+ caracteres, mayúsc/minúsc/número), cambio de clave obligatorio para cuentas con clave temporal, secreto 2FA cifrado con AES-256-GCM, carpetas `tools/`, `storage/`, `includes/` y `docs/` bloqueadas por `.htaccess`; los respaldos se guardan fuera del directorio web en `/var/backups/kardex` y se cifran con GnuPG/AES-256.

## Decisiones (según lo acordado)
- Las cuentas iniciales de `admin`/demo pueden ingresar una primera vez con su credencial de instalación, pero si mantienen la contraseña conocida `123456`, el script de migración obliga a cambiarla antes de usar el sistema. En Ubuntu se utilizará un usuario propio de MariaDB con privilegios mínimos, nunca `root` desde la aplicación.
- Se incorporó la relación `materia_cursos`: el administrador asigna cada materia a uno o varios cursos. El docente solo consulta estudiantes y calificaciones de los cursos asociados a sus materias. La migración deduce relaciones existentes a partir de las notas registradas; revisa manualmente las materias/cursos que aún no tengan notas.
- **Auditoría de operaciones críticas:** cambios de identidad/rol, contraseñas, MFA, notas, restauraciones, configuración y cuenta de emergencia se ejecutan en transacciones; si no se puede guardar la auditoría, se revierte la operación.
- **Docentes y notas:** eliminar un docente ya no borra sus calificaciones. «Dar de baja» revoca la identidad (pierde el acceso), conserva el registro y las notas, y libera sus materias para reasignarlas. Lo mismo para estudiantes. Una materia con notas no se puede eliminar.

## Pendiente para el final
Políticas 1-8 (Parte 7), tabla de controles verificables (Parte 8), sección F (criticidad) y despliegue en la VM Linux (HTTPS, firewall, usuario de BD mínimo, configuración segura de respaldos y cron; ver `docs/BACKUP_UBUNTU.md`).
