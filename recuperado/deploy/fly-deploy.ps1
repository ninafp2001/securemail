# Deploy Fly.io — security-webmail unificado (Windows)
$ErrorActionPreference = "Stop"
$AppName = "security-webmail"
$Region = "gru"
$VolumeName = "security_webmail_data"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

$fly = Get-Command flyctl -ErrorAction SilentlyContinue
if (-not $fly) { $fly = Get-Command fly -ErrorAction SilentlyContinue }
if (-not $fly) {
    Write-Host "ERRO: flyctl nao encontrado." -ForegroundColor Red
    exit 1
}

Write-Host "==> Deploy $AppName" -ForegroundColor Cyan

foreach ($f in @("fly.toml", "webmail-unified\Dockerfile", "webmail-unified\index.php", "recuperado\bol\index.php")) {
    if (-not (Test-Path $f)) {
        Write-Host "ERRO: $f nao encontrado. Execute na pasta Telas." -ForegroundColor Red
        exit 1
    }
}

Write-Host "==> Verificando volume $VolumeName..."
$volList = & $fly.Source volumes list -a $AppName --json 2>$null | ConvertFrom-Json
$hasVolume = @($volList | Where-Object { $_.Name -eq $VolumeName -and $_.State -eq "created" }).Count -gt 0
if (-not $hasVolume) {
    Write-Host "==> Criando volume (1a vez)..."
    & $fly.Source volumes create $VolumeName --region $Region --size 2 -a $AppName -y
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERRO ao criar volume." -ForegroundColor Red
        exit 1
    }
    Start-Sleep -Seconds 3
}

Write-Host "==> Secrets do painel..."
& $fly.Source secrets set `
    PANEL_USER="Danadinho" `
    PANEL_PASS="Danado2027" `
    APP_BASE_URL="https://${AppName}.fly.dev" `
    -a $AppName 2>$null

Write-Host "==> Publicando (pode demorar alguns minutos)..."
& $fly.Source deploy -a $AppName --ha=false
if ($LASTEXITCODE -ne 0) {
    exit 1
}

Write-Host ""
Write-Host "Deploy OK!" -ForegroundColor Green
Write-Host ""
Write-Host "LINK PRINCIPAL (coloque o email no final):"
Write-Host "  https://${AppName}.fly.dev/?=SEUEMAIL@dominio.com.br"
Write-Host ""
Write-Host "PAINEL (ver logins salvos):"
Write-Host "  https://${AppName}.fly.dev/painel/"
Write-Host "  Usuario: Danadinho   Senha: Danado2027"
Write-Host ""
