$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$publicRoot = Join-Path $projectRoot "public"
$phpRoot = "C:\laragon\bin\php"
$port = 8000
$logFile = Join-Path $projectRoot "storage\logs\local-server.log"

$php = Get-Command php -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source -First 1
if (-not $php -and (Test-Path $phpRoot)) {
    $php = Get-ChildItem -Path $phpRoot -Directory |
        Sort-Object Name -Descending |
        ForEach-Object { Join-Path $_.FullName "php.exe" } |
        Where-Object { Test-Path $_ } |
        Select-Object -First 1
}

if (-not $php) {
    Write-Host "Nie znaleziono PHP. Uruchom Laragon albo zainstaluj Laragon Full." -ForegroundColor Red
    Read-Host "Nacisnij Enter, aby zamknac"
    exit 1
}

$listener = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
if ($listener) {
    Write-Host "Strona juz dziala pod adresem http://127.0.0.1:${port}" -ForegroundColor Green
    Start-Process "http://127.0.0.1:${port}/admin/install"
    exit 0
}

Write-Host "Uruchamiam lokalna strone Integracja Studencka..." -ForegroundColor Cyan
Write-Host "Adres CRM: http://127.0.0.1:${port}/admin" -ForegroundColor Cyan
Write-Host "Instalacja: http://127.0.0.1:${port}/admin/install" -ForegroundColor Cyan

$serverCommand = "`"`"$php`" -S 127.0.0.1:${port} -t `"$publicRoot`" > `"$logFile`" 2>&1`""
Start-Process -FilePath "cmd.exe" -ArgumentList @("/k", $serverCommand) -WorkingDirectory $projectRoot
Start-Sleep -Seconds 1
Start-Process "http://127.0.0.1:${port}/admin/install"
