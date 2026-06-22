@echo off
chcp 65001 >nul
title TRASET - Quick Start Guide

echo.
echo ============================================
echo    TRASET - TRAnsaksi & ASET Management
echo ============================================
echo.
echo.
echo    Langkah Setup (Sekali saja):
echo    -------------------------------
echo    1. Buat database 'traset' di PostgreSQL
echo    2. Jalankan: 2-Setup-Database.bat
echo.
echo.
echo    Menjalankan Aplikasi:
echo    ---------------------
echo    1. Jalankan: 1-Start-Server.bat
echo    2. Tunggu sampai server running
echo    3. Klik: Buka-TRASET.url
echo.
echo.
echo    Akun Demo:
echo    ----------
echo    Super Admin (BMN Kantor):  superadmin@bmn.go.id / password
echo    Staff BMN (BMN Gudang):    staff@bmn.go.id / password
echo    User (Peminjam):           user@bmn.go.id / password
echo.
echo.
echo    Tekan ENTER untuk membuka panduan lengkap...
pause >nul

REM Open QUICK-START.md in default text editor
start "" "QUICK-START.md"