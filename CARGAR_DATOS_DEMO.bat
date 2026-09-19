@echo off
REM ============================================================
REM  Carga datos de demostracion para el dashboard del restaurante
REM  Ejecuta el seeder DashboardDemoSeeder y guarda el resultado
REM  en _seed_demo_log.txt
REM ============================================================
cd /d "%~dp0"
set "LOG=%~dp0_seed_demo_log.txt"

echo ===== Ejecutando DashboardDemoSeeder ===== > "%LOG%"
echo Fecha: %date% %time% >> "%LOG%"
echo. >> "%LOG%"

set "PHPEXE="
where php >nul 2>nul && set "PHPEXE=php"
if not defined PHPEXE if exist "C:\xampp\php\php.exe" set "PHPEXE=C:\xampp\php\php.exe"
if not defined PHPEXE for /d %%D in ("C:\laragon\bin\php\*") do if exist "%%D\php.exe" set "PHPEXE=%%D\php.exe"
if not defined PHPEXE if exist "C:\wamp64\bin\php\php.exe" set "PHPEXE=C:\wamp64\bin\php\php.exe"

if not defined PHPEXE (
  echo ERROR: No se encontro php.exe en el PATH ni en rutas comunes ^(XAMPP/Laragon/WAMP^). >> "%LOG%"
  echo Abre una terminal en esta carpeta y ejecuta manualmente: >> "%LOG%"
  echo    php artisan db:seed --class=DashboardDemoSeeder --force >> "%LOG%"
  echo. >> "%LOG%"
  echo ===== Fin con error ===== >> "%LOG%"
  echo No se encontro PHP. Revisa _seed_demo_log.txt
  pause
  exit /b 1
)

echo Usando PHP: %PHPEXE% >> "%LOG%"
echo. >> "%LOG%"
"%PHPEXE%" artisan db:seed --class=DashboardDemoSeeder --force >> "%LOG%" 2>&1

echo. >> "%LOG%"
echo ===== Fin ===== >> "%LOG%"
echo Listo. Revisa _seed_demo_log.txt para ver el resultado.
pause
