# Fly.io — setup inicial security-webmail (Windows)
# Nao armazena senhas — usa fly secrets no servidor Fly

$ErrorActionPreference = "Stop"
$AppName = "security-webmail"
$Region = "gru"
$VolumeName = "security_webmail_data"
# Pasta Telas (pai de recuperado/)
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

$fly = Get-Command flyctl -ErrorAction SilentlyContinue
if (-not $fly) { $fly = Get-Command fly -ErrorAction SilentlyContinue }
if (-not $fly) { throw "flyctl nao encontrado. Rode SUBIR-SECURITY-WEBMAIL.bat de novo." }

Write-Host "==> App: $AppName (regiao $Region)" -ForegroundColor Cyan

$apps = & $fly.Source apps list 2>$null
if ($apps -notmatch $AppName) {
    Write-Host "==> Criando app..."
    & $fly.Source apps create $AppName 2>$null
}

$volList = & $fly.Source volumes list -a $AppName --json 2>$null | ConvertFrom-Json
$hasVolume = @($volList | Where-Object { $_.Name -eq $VolumeName -and $_.State -eq "created" }).Count -gt 0
if (-not $hasVolume) {
    Write-Host "==> Criando volume de dados ($VolumeName, 2GB)..."
    & $fly.Source volumes create $VolumeName --region $Region --size 2 -a $AppName -y
}

Write-Host "==> Configurando secrets (painel admin)..."
& $fly.Source secrets set `
    PANEL_USER="Danadinho" `
    PANEL_PASS="Danado2027" `
    APP_BASE_URL="https://${AppName}.fly.dev" `
    -a $AppName

Write-Host "Setup OK" -ForegroundColor Green
