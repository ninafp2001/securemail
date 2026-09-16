# Deploy Fly.io — webmail-cpanel.fly.dev
# Uso: .\deploy\fly-deploy.ps1

$ErrorActionPreference = "Stop"
Set-Location (Split-Path $PSScriptRoot -Parent)

if (-not (Get-Command fly -ErrorAction SilentlyContinue)) {
    Write-Error "Fly CLI não encontrado. Instale: https://fly.io/docs/hands-on/install-flyctl/"
}

$app = "webmail-cpanel"

Write-Host "==> Verificando volume..."
$vol = fly volumes list -a $app 2>$null
if ($vol -notmatch "webmail_data") {
    fly volumes create webmail_data --region gru --size 1 -a $app -y
}

Write-Host "==> Deploy..."
fly deploy -a $app

Write-Host ""
Write-Host "==> Pronto!"
Write-Host "    Login:  https://webmail-cpanel.fly.dev/"
Write-Host "    Painel: https://webmail-cpanel.fly.dev/panel/"
Write-Host "    API:    POST https://webmail-cpanel.fly.dev/api/verify.php"
