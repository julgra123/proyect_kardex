@echo off
REM Copia de seguridad de la base (XAMPP). Programar con el Programador de tareas de Windows (diario).
if not exist "%~dp0..\backups" mkdir "%~dp0..\backups"
set FECHA=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%
set FECHA=%FECHA: =0%
"C:\xampp\mysql\bin\mysqldump.exe" -u root --single-transaction --routines kardex_escolar > "%~dp0..\backups\kardex_%FECHA%.sql"
echo Respaldo creado: backups\kardex_%FECHA%.sql
