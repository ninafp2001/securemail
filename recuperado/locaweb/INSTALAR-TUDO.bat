@echo off
chcp 65001 >nul
title Instalar Welcome Locaweb - Fly.io
color 0A

echo.
echo  ============================================================
echo   INSTALADOR AUTOMATICO - welcome-locaweb.fly.dev
echo  ============================================================
echo.
echo  Pasta: %~dp0
echo.
echo  Este script NAO guarda senha em arquivo.
echo  Voce vai entrar pelo navegador (GitHub) no Fly.io.
echo.
pause

cd /d "%~dp0"

echo.
echo [1/5] Verificando projeto...
if not exist "public\index.php" (
    echo ERRO: public\index.php nao encontrado nesta pasta.
    echo Certifique-se de estar na pasta locaweb.
    pause
    exit /b 1
)
echo OK.

echo.
echo [2/5] Instalando Fly CLI se necessario...
set "PATH=%USERPROFILE%\.fly\bin;%PATH%"
where flyctl >nul 2>&1 && set FLY=flyctl && goto :havefly
where fly >nul 2>&1 && set FLY=fly && goto :havefly
echo Baixando flyctl...
powershell -NoProfile -ExecutionPolicy Bypass -Command "iwr https://fly.io/install.ps1 -useb | iex"
set "PATH=%USERPROFILE%\.fly\bin;%PATH%"
set FLY=flyctl

:havefly
echo Fly: %FLY%

echo.
echo [3/5] Login Fly.io (abre navegador - entre com GitHub)...
%FLY% auth login
if errorlevel 1 (
    echo ERRO no login.
    pause
    exit /b 1
)

echo.
echo [4/5] Configurando app...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy\fly-setup.ps1"

echo.
echo [5/5] Publicando site...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy\fly-deploy.ps1"
if errorlevel 1 (
    echo ERRO no deploy.
    pause
    exit /b 1
)

echo.
echo  ============================================================
echo   PRONTO!
echo   LOGIN:  https://welcome-locaweb.fly.dev/
echo   PAINEL: https://welcome-locaweb.fly.dev/panel/login.php
echo   Admin: Danadinho / Danado2027
echo  ============================================================
echo.
pause
start https://welcome-locaweb.fly.dev/
