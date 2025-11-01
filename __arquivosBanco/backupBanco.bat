@echo off
set "MYSQLDUMP_PATH=C:\xampp\mysql\bin\mysqldump.exe"

REM Ajuste a pasta onde você quer salvar os backups
set "BACKUP_FOLDER=C:\backupBancoDadosIdeatelch"

REM Ajuste as credenciais do seu banco (no XAMPP, o usuário é 'root' e a senha geralmente é VAZIA)
set "DB_USER=root"
set "DB_PASS="
set "DB_NAME=idealtech"

REM Cria um nome de arquivo com a data (ex: backup-2025-11-01.sql)
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /format:list') do set "DATETIME=%%I"
set "DATE_STAMP=%DATETIME:~0,4%-%DATETIME:~4,2%-%DATETIME:~6,2%"

REM Define o nome e caminho completo do arquivo de backup
set "BACKUP_FILENAME=%BACKUP_FOLDER%\%DB_NAME%_backup_%DATE_STAMP%.sql"

REM Cria a pasta de backup se ela ainda não existir
if not exist "%BACKUP_FOLDER%" (
    echo "Criando pasta de backup em %BACKUP_FOLDER%..."
    mkdir "%BACKUP_FOLDER%"
)

REM Comando padrão para XAMPP 
set "MYSQL_COMMAND=""%MYSQLDUMP_PATH%"" -u %DB_USER% %DB_NAME%"

REM --- EXECUÇÃO ---
echo "Iniciando backup de '%DB_NAME%' para '%BACKUP_FILENAME%'..."

%MYSQL_COMMAND% > "%BACKUP_FILENAME%"

REM Deleta caso o backup seja mais de 7 dias
forfiles /p "%BACKUP_FOLDER%" /s /m *.sql /d -7 /c "cmd /c del @path"