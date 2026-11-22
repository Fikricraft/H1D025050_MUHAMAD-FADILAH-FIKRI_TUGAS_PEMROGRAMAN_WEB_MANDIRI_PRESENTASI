@echo off
title IKAN// PROTOCOL - Local Server
color 0B
cd /d "%~dp0"

echo ========================================================
echo   IKAN// PROTOCOL - SERVER LOKAL (PORT 8000)
echo   Folder proyek : %CD%
echo ========================================================
echo.
echo CATATAN:
echo   1. Nyalakan MySQL di XAMPP Control Panel (opsional).
echo      Tanpa MySQL aplikasi tetap jalan dalam MODE DEMO.
echo   2. Login memakai username: admin  /  password: password123
echo.
echo Membuka browser ke http://localhost:8000/login.php ...
start "" http://localhost:8000/login.php
echo.
echo Server sedang berjalan. JANGAN tutup jendela ini saat presentasi.
echo Tekan CTRL + C untuk mematikan server.
echo.

"C:\xampp\php\php.exe" -S localhost:8000 -t "%CD%"

echo.
echo [INFO] Server berhenti atau PHP tidak ditemukan di C:\xampp\php\php.exe.
pause
