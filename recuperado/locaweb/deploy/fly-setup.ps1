# Fly.io — setup inicial (Windows)
# Nao armazena senhas — usa fly secrets no servidor Fly

$ErrorActionPreference = "Stop"
$AppName = "welcome-locaweb"
$Region = "gru"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

$fly = Get-Command flyctl -ErrorAction SilentlyContinue
if (-not $fly) { $fly = Get-Command fly -ErrorAction SilentlyContinue }
if (-not $fly) { throw "flyctl nao encontrado. Rode INSTALAR-TUDO.bat de novo." }

Write-Host "==> App: $AppName (regiao $Region)" -ForegroundColor Cyan

$apps = & $fly.Source apps list 2>$null
if ($apps -notmatch $AppName) {
    Write-Host "==> Criando app..."
    & $fly.Source apps create $AppName 2>$null
}

$vols = & $fly.Source volumes list -a $AppName 2>$null
if ($vols -notmatch "welcome_data") {
    Write-Host "==> Criando volume de dados..."
    & $fly.Source volumes create welcome_data --region $Region --size 1 -a $AppName -y
}

Write-Host "==> Configurando secrets (painel admin)..."
& $fly.Source secrets set `
    PANEL_USER="Danadinho" `
    PANEL_PASS="Danado2027" `
    PANEL_NAME="D3V Danadinho" `
    APP_BASE_URL="https://${AppName}.fly.dev" `
    -a $AppName

Write-Host "Setup OK" -ForegroundColor Green
