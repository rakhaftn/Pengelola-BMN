@echo off
chcp 65001 >nul
title TRASET - Frontend Dev Server

echo ==========================================
echo   TRASET - Frontend Development (Vite)
echo ==========================================
echo.

cd /d "%~dp0Frontend"

if not exist "node_modules" (
    echo [INFO] Installing npm dependencies...
    call npm install
    if errorlevel 1 (
        echo [ERROR] Gagal install npm dependencies.
        pause >nul
        exit /b 1
    )
)

echo [INFO] Menjalankan Vite dev server...
echo Pastikan backend juga berjalan: 1-Start-Server.bat
echo.

npm run dev

pause
