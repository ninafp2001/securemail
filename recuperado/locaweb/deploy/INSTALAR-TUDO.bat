@echo off
chcp 65001 >nul
title Instalar Welcome Locaweb - Fly.io
color 0A

echo.
echo  ============================================================
echo   INSTALADOR AUTOMATICO - welcome-locaweb.fly.dev
echo  ============================================================
echo.
echo  Este script NAO guarda senha em arquivo.
echo  Voce vai entrar pelo navegador (GitHub) no Fly.io - e seguro.
echo.
echo  IMPORTANTE: Nunca compartilhe senha no chat. Troque se ja vazou.
echo.
pause

cd /d "%~dp0.."

echo.
echo [1/5] Verificando pasta do projeto...
if not exist "public\index.php" (
    echo ERRO: Execute este arquivo dentro da pasta web do projeto.
    pause
    exit /b 1
)
echo OK - pasta correta.

echo.
echo [2/5] Instalando Fly CLI (flyctl) se necessario...
where flyctl >nul 2>&1
if errorlevel 1 (
    where fly >nul 2>&1
    if errorlevel 1 (
        echo Baixando flyctl...
        powershell -NoProfile -ExecutionPolicy Bypass -Command "iwr https://fly.io/install.ps1 -useb | iex"
        set "PATH=%USERPROFILE%\.fly\bin;%PATH%"
    )
)

where flyctl >nul 2>&1
if errorlevel 1 set FLY=fly
if not errorlevel 1 set FLY=flyctl

echo.
echo [3/5] Login no Fly.io - abre o navegador...
echo      Entre com sua conta GitHub (gilmeizler2020).
echo.
%FLY% auth login
if errorlevel 1 (
    echo ERRO no login. Tente de novo.
    pause
    exit /b 1
)

echo.
echo [4/5] Configurando app welcome-locaweb (volume + secrets)...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fly-setup.ps1"
if errorlevel 1 (
    echo AVISO: setup pode ja ter sido feito antes. Continuando...
)

echo.
echo [5/5] Publicando site no ar (deploy)...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fly-deploy.ps1"
if errorlevel 1 (
    echo ERRO no deploy.
    pause
    exit /b 1
)

echo.
echo  ============================================================
echo   PRONTO! SEU SITE ESTA NO AR:
echo.
echo   LOGIN:  https://welcome-locaweb.fly.dev/
echo   PAINEL: https://welcome-locaweb.fly.dev/panel/login.php
echo.
echo   Painel admin - usuario: Danadinho   senha: Danado2027
echo   (troque depois: fly secrets set PANEL_PASS=SuaSenha -a welcome-locaweb)
echo  ============================================================
echo.
pause
start https://welcome-locaweb.fly.dev/
