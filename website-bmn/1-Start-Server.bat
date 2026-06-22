@echo off
chcp 65001 >nul
title TRASET - Laravel Development Server

echo ==========================================
echo   TRASET - Starting Development Server
echo ==========================================
echo.

cd /d "%~dp0Backend"

REM Add XAMPP PHP to PATH
set PATH=C:\xampp\php;C:\xampp\apache\bin;%PATH%

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP not found. Pastikan XAMPP PHP sudah terinstall.
    echo Tekan tombol apapun untuk keluar...
    pause >nul
    exit /b 1
)

echo [OK] PHP Version:
php -v | findstr /v "PHP"
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

REM Clear and optimize cache
echo [INFO] Clearing cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo.

REM Run migrations
echo [INFO] Running migrations...
php artisan migrate --force
echo.

REM Start the server
echo ==========================================
echo   Server akan berjalan di:
echo   http://localhost:8000
echo ==========================================
echo.
echo Tekan CTRL+C untuk menghentikan server
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
