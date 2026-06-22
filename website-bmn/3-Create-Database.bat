@echo off
chcp 65001 >nul
title TRASET - Create Database

echo ==========================================
echo   TRASET - Membuat Database PostgreSQL
echo ==========================================
echo.

REM Add PostgreSQL to PATH
set PATH=C:\xampp\pgsql\bin;%PATH%

REM Ask for database name
set /p DBNAME="Nama database (default: traset): "
if "%DBNAME%"=="" set DBNAME=traset

REM Ask for postgres user
set /p PGUSER="PostgreSQL user (default: postgres): "
if "%PGUSER%"=="" set PGUSER=postgres

echo.
echo [INFO] Membuat database '%DBNAME%'...
echo.

"C:\xampp\pgsql\bin\psql.exe" -U %PGUSER% -c "CREATE DATABASE %DBNAME%;"
if errorlevel 1 (
    echo.
    echo [WARNING] Database mungkin sudah ada atau ada error.
    echo.
) else (
    echo.
    echo [SUCCESS] Database '%DBNAME%' berhasil dibuat!
    echo.
)

echo Tekan ENTER untuk melanjutkan...
pause >nul
