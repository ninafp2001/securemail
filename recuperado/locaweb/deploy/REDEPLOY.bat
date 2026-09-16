@echo off
REM Redeploy rapido - welcome-locaweb.fly.dev
chcp 65001 >nul
title Redeploy welcome-locaweb
cd /d "%~dp0.."
set "PATH=%USERPROFILE%\.fly\bin;%PATH%"

echo.
echo  Corrigindo site - publicando de novo...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fly-deploy.ps1"
if errorlevel 1 (
    echo ERRO no deploy.
    pause
    exit /b 1
)

echo.
echo  PRONTO! Abra: https://welcome-locaweb.fly.dev/
echo.
pause
start https://welcome-locaweb.fly.dev/
