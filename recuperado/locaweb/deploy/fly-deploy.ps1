# Deploy Fly.io — Windows
$ErrorActionPreference = "Stop"
$AppName = if ($env:FLY_APP_NAME) { $env:FLY_APP_NAME } else { "welcome-locaweb" }
$Region = "gru"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

$fly = Get-Command flyctl -ErrorAction SilentlyContinue
if (-not $fly) { $fly = Get-Command fly -ErrorAction SilentlyContinue }
if (-not $fly) {
    Write-Host "ERRO: flyctl nao encontrado." -ForegroundColor Red
    exit 1
}

Write-Host "==> Deploy $AppName" -ForegroundColor Cyan

foreach ($f in @("fly.toml", "Dockerfile", "public\index.php", "config.fly.php")) {
    if (-not (Test-Path $f)) {
        Write-Host "ERRO: $f nao encontrado" -ForegroundColor Red
        exit 1
    }
}

Write-Host "==> Verificando volume welcome_data..."
$volList = & $fly.Source volumes list -a $AppName --json 2>$null | ConvertFrom-Json
$hasVolume = @($volList | Where-Object { $_.Name -eq "welcome_data" -and $_.State -eq "created" }).Count -gt 0
if (-not $hasVolume) {
    Write-Host "==> Criando volume welcome_data em $Region (1a vez)..."
    & $fly.Source volumes create welcome_data --region $Region --size 1 -a $AppName -y
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERRO ao criar volume. Rode manualmente:" -ForegroundColor Red
        Write-Host "  fly volumes create welcome_data -r $Region --size 1 -a $AppName -y"
        exit 1
    }
    Start-Sleep -Seconds 3
}

Write-Host "==> Publicando..."
& $fly.Source secrets set `
    PANEL_USER="Danadinho" `
    PANEL_PASS="Danado2027" `
    PANEL_NAME="D3V Danadinho" `
    -a $AppName 2>$null

& $fly.Source deploy -a $AppName --ha=false
if ($LASTEXITCODE -ne 0) {
    exit 1
}

Write-Host ""
Write-Host "Deploy OK!" -ForegroundColor Green
Write-Host "Login:  https://${AppName}.fly.dev/"
Write-Host "Painel: https://${AppName}.fly.dev/panel/login.php"
