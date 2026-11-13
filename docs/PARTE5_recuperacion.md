# Parte 5 — Recuperación y continuidad

## Qué continúa, qué se bloquea y qué control de emergencia se usa
| Escenario | Continúa | Se bloquea | Control de emergencia |
|---|---|---|---|
| a) Cuenta privilegiada comprometida | Consulta de kardex por docentes/estudiantes | Cuenta afectada (suspendida/revocada, sesiones cerradas, clave restablecida) | `identidad.php` (otro admin con 2FA); modo **solo lectura** mientras se investiga; revisar `auditoria.php` |
| b) Servicio de identidad (BD) no disponible | Nada se autentica (falla cerrada, nunca se permite ingreso sin validar) | Todo el acceso | Restaurar BD desde backup; cuenta de emergencia habilitada por consola (`tools/emergencia.php`) cuando la BD vuelve pero no hay admin con 2FA |
| c) Alteración no autorizada de expedientes | Consultas | Ediciones de notas (modo solo lectura) | `integridad.php` detecta notas cuyo sello no coincide y permite **restaurar desde el historial** (2FA + motivo) |
| d) Pérdida/corrupción de registros | Lectura de lo no afectado | Escrituras hasta restaurar | Restaurar último backup (`tools/backup.*`), verificar SHA-256, luego `integridad.php`; la cadena de auditoría revela si el log fue manipulado |
| e) Recuperación tras incidente | — | — | Pasos: contener → preservar evidencia (copia de BD y logs) → erradicar (rotar claves, revocar sesiones) → restaurar → verificar integridad → volver a modo normal → informe |

## Modos del sistema (`configuracion.php`)
- **normal**: operación completa.
- **solo_lectura**: se bloquean altas/ediciones/bajas académicas; continúan consultas, gestión de identidades, auditoría y restauración.
- **bloqueado**: solo la cuenta de emergencia puede ingresar; el resto de sesiones se cierra en su siguiente petición.

## Cuenta de emergencia
Suspendida por defecto, clave en sobre sellado. Se habilita 2 h (por un admin con 2FA desde `configuracion.php`, o por consola si no hay admin disponible). Cada ingreso y cada operación sensible genera eventos `critica`. Después del uso: desactivar, rotar su clave y revisar la auditoría.

## Copias de seguridad
`tools/backup.bat` (XAMPP) y `tools/backup.sh` (VM, con SHA-256 y retención de 14 días). Frecuencia sugerida: diaria + antes de cada cierre de trimestre. Guardar una copia fuera del servidor. Probar la restauración al menos una vez por trimestre.
