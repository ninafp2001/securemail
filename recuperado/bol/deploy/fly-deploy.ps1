# Deploy uolpro-webmail no Fly.io
$ErrorActionPreference = "Stop"
Set-Location (Split-Path $PSScriptRoot -Parent)

$app = "uolpro-webmail"
Write-Host "Deploy $app ..." -ForegroundColor Cyan

fly deploy -a $app
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host ""
Write-Host "Site:   https://$app.fly.dev/" -ForegroundColor Green
Write-Host "Painel: https://$app.fly.dev/panel/" -ForegroundColor Green
Write-Host "GitHub: https://github.com/gilmeizler2020/uolpro-webmail" -ForegroundColor Green
