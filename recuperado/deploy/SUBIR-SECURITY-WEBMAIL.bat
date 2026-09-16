@echo off
chcp 65001 >nul
title Subir Security-Webmail - Fly.io
color 0A

echo.
echo  ============================================================
echo   DEPLOY AUTOMATICO - security-webmail.fly.dev
echo   Todas as telas (Terra, UOL, BOL, Locaweb...) numa maquina
echo  ============================================================
echo.
echo  Este script NAO guarda senha em arquivo.
echo  Voce entra pelo navegador (GitHub) no Fly.io.
echo.
pause

cd /d "%~dp0..\.."

echo.
echo [1/5] Verificando pasta do projeto...
if not exist "fly.toml" (
    echo ERRO: fly.toml nao encontrado. Abra a pasta Telas correta.
    pause
    exit /b 1
)
if not exist "recuperado\bol\index.php" (
    echo ERRO: pasta recuperado\ nao encontrada.
    pause
    exit /b 1
)
echo OK - pasta Telas correta.

echo.
echo [2/5] Instalando Fly CLI se necessario...
where fly >nul 2>&1
if errorlevel 1 (
    echo Baixando flyctl...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "iwr https://fly.io/install.ps1 -useb | iex"
    set "PATH=%USERPROFILE%\.fly\bin;%PATH%"
)
set FLY=fly

echo.
echo [3/5] Login no Fly.io - abre o navegador...
echo      Entre com sua conta GitHub.
echo.
%FLY% auth login
if errorlevel 1 (
    echo ERRO no login. Tente de novo.
    pause
    exit /b 1
)

echo.
echo [4/5] Configurando app (volume + senhas do painel)...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fly-setup.ps1"
if errorlevel 1 (
    echo AVISO: setup pode ja ter sido feito. Continuando...
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
echo   PRONTO! SITE NO AR:
echo.
echo   TELAS (coloque email no final):
echo   https://security-webmail.fly.dev/?=email@dominio.com.br
echo.
echo   PAINEL (ver logins):
echo   https://security-webmail.fly.dev/painel/
echo   Usuario: Danadinho   Senha: Danado2027
echo.
echo   Leia: recuperado\LEIA-ME-TESTAR-PROVEDORES.txt
echo  ============================================================
echo.
pause
start https://security-webmail.fly.dev/painel/
