#!/usr/bin/env bash
# =============================================================================
# Fly.io — deploy produção
# App: welcome-locaweb → https://welcome-locaweb.fly.dev/
# GitHub: https://github.com/gilmeizler2020/welcome/tree/main/web
# =============================================================================
set -euo pipefail

APP_NAME="${FLY_APP_NAME:-welcome-locaweb}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

cd "${ROOT_DIR}"

if ! command -v flyctl >/dev/null 2>&1 && ! command -v fly >/dev/null 2>&1; then
  echo "ERRO: Instale flyctl: https://fly.io/docs/flyctl/install/"
  exit 1
fi

FLY="$(command -v flyctl 2>/dev/null || command -v fly)"

echo "==> Deploy ${APP_NAME} a partir de ${ROOT_DIR}"
echo "    URL: https://${APP_NAME}.fly.dev/"

echo "==> Validando arquivos..."
test -f fly.toml || { echo "ERRO: fly.toml não encontrado"; exit 1; }
test -f Dockerfile || { echo "ERRO: Dockerfile não encontrado"; exit 1; }
test -f public/index.php || { echo "ERRO: public/index.php não encontrado"; exit 1; }
test -f config.fly.php || { echo "ERRO: config.fly.php não encontrado"; exit 1; }

echo "==> Publicando no Fly.io..."
$FLY deploy -a "${APP_NAME}" --ha=false

echo ""
echo "=============================================="
echo " Deploy OK!"
echo " Login:  https://${APP_NAME}.fly.dev/"
echo " Painel: https://${APP_NAME}.fly.dev/panel/login.php"
echo " API:    https://${APP_NAME}.fly.dev/api/verify.php"
echo " Sucesso login → https://webmail-seguro.com.br/v2/"
echo ""
echo " Logs:  fly logs -a ${APP_NAME}"
echo " SSH:   fly ssh console -a ${APP_NAME}"
echo "=============================================="
