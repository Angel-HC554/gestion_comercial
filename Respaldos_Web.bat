@echo off
:: Configuración general
set DB_USER=root
set DB_PASS=
set BACKUP_DIR=C:\Respaldos_bd
set MYSQL_BIN=C:\xampp\mysql\bin

:: Generar prefijos de fecha y hora
set FECHA=%date:~6,4%%date:~3,2%%date:~0,2%
set HORA=%time:~0,2%%time:~3,2%%time:~6,2%
set HORA=%HORA: =0%

:: Crear la carpeta principal si no existe
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: Configurar credenciales para los comandos
if "%DB_PASS%"=="" (
    set AUTH=-u %DB_USER%
) else (
    set AUTH=-u %DB_USER% -p%DB_PASS%
)

echo Iniciando proceso de respaldo multiple...
echo ------------------------------------------

:: 1. Guardar la lista de bases de datos en un archivo temporal
"%MYSQL_BIN%\mysql.exe" %AUTH% -s -N -e "SHOW DATABASES" > "%BACKUP_DIR%\temp_dbs.txt"

:: 2. Leer el archivo temporal y omitir las de sistema
for /f "tokens=*" %%D in ('findstr /V /I /C:"information_schema" /C:"performance_schema" /C:"mysql" /C:"phpmyadmin" /C:"test" "%BACKUP_DIR%\temp_dbs.txt"') do (
    
    :: Crear la subcarpeta con el nombre exacto de la base de datos si no existe
    if not exist "%BACKUP_DIR%\%%D" mkdir "%BACKUP_DIR%\%%D"
    
    echo [OK] Respaldando: %%D ...
    :: Guardar el archivo .sql dentro de su respectiva subcarpeta
    "%MYSQL_BIN%\mysqldump.exe" %AUTH% %%D > "%BACKUP_DIR%\%%D\%%D_%FECHA%_%HORA%.sql"
)

:: 3. Borrar el archivo temporal para no dejar basura
del "%BACKUP_DIR%\temp_dbs.txt"

echo ------------------------------------------
echo Limpiando respaldos antiguos (mas de 30 dias)...
:: Agregamos el parametro /s para que busque dentro de las subcarpetas
forfiles /p "%BACKUP_DIR%" /s /m *.sql /d -30 /c "cmd /c del @path" 2>nul

echo Proceso completado exitosamente.