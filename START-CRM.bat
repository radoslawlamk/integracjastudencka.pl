@echo off
setlocal
cd /d "%~dp0"

set "PHP_EXE="
if exist "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" (
  set "PHP_EXE=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
)

if "%PHP_EXE%"=="" (
  for /d %%D in ("C:\laragon\bin\php\php-*") do (
    if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
  )
)

if "%PHP_EXE%"=="" (
  echo Nie znaleziono PHP w Laragonie.
  echo Uruchom Laragon albo zainstaluj Laragon Full.
  pause
  exit /b 1
)

echo.
echo Integracja Studencka CRM
echo.
echo Nie zamykaj tego okna podczas pracy z lokalna strona.
echo.
echo Otworz w przegladarce:
echo http://127.0.0.1:8000/admin/install
echo.

start "" "http://127.0.0.1:8000/admin/install"
"%PHP_EXE%" -S 127.0.0.1:8000 -t "%~dp0public"

echo.
echo Serwer lokalny zostal zamkniety.
pause
