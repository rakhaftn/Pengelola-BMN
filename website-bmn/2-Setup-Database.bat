@echo off
chcp 65001 >nul
title TRASET - Setup Database

echo ==========================================
echo   TRASET - Database Setup
echo ==========================================
echo.

cd /d "%~dp0Backend"

REM Add XAMPP PHP to PATH
set PATH=C:\xampp\php;C:\xampp\pgsql;%PATH%

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP not found. Pastikan XAMPP PHP sudah terinstall.
    echo Tekan tombol apapun untuk keluar...
    pause >nul
    exit /b 1
)

echo [OK] PHP tersedia
echo.

REM Check if dependencies are installed
if not exist "vendor\autoload.php" (
    echo [INFO] Installing dependencies...
    echo.
    call composer install --no-interaction
    if errorlevel 1 (
        echo [ERROR] Gagal install dependencies.
        echo Tekan tombol apapun untuk keluar...
        pause >nul
        exit /b 1
    )
)

echo.
echo ==========================================
echo   Langkah Setup:
echo   1. Membuat database 'traset'
echo   2. Running migrations
echo   3. Seeding data contoh
echo ==========================================
echo.

REM Ask user to create database (PostgreSQL)
echo [INFO] Membuat database 'traset' di PostgreSQL...
echo [INFO] Jika belum ada, buka pgAdmin dan jalankan:
echo        CREATE DATABASE traset;
echo.
echo Tekan ENTER untuk melanjutkan...
pause >nul

REM Run migrations
echo [INFO] Running migrations...
php artisan migrate --force
if errorlevel 1 (
    echo [ERROR] Gagal running migrations. Pastikan database 'traset' sudah dibuat.
    echo Tekan tombol apapun untuk keluar...
    pause >nul
    exit /b 1
)
echo.

REM Seed data
echo [INFO] Seeding data...
php artisan db:seed --force
echo.

echo ==========================================
echo   Setup Selesai!
echo ==========================================
echo.
echo Database 'traset' sudah siap dengan:
echo - Roles dan Permissions
echo - Struktur Lokasi (Direktorat, Gedung, Lantai, Lokasi, Ruangan)
echo - Data contoh (User, Kategori, Barang, Peminjaman)
echo.
echo Sekarang jalankan: 1-Start-Server.bat
echo.
pause
