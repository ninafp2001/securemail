#!/usr/bin/env bash
# =============================================================================
# Fly.io — configuração inicial (rodar UMA VEZ)
# App: welcome-locaweb
# URL:  https://welcome-locaweb.fly.dev/
# Repo: https://github.com/gilmeizler2020/welcome/tree/main/web
# =============================================================================
set -euo pipefail

APP_NAME="${FLY_APP_NAME:-welcome-locaweb}"
REGION="${FLY_REGION:-gru}"
PANEL_USER="${PANEL_USER:-Lima}"
PANEL_PASS="${PANEL_PASS:-Lima}"
PANEL_NAME="${PANEL_NAME:-D3V L1m4}"
PANEL_RESET_KEY="${PANEL_RESET_KEY:-$(openssl rand -hex 16 2>/dev/null || echo fly-reset-key)}"

echo "==> Fly.io setup: ${APP_NAME}"
echo "    Região: ${REGION}"
echo "    URL: https://${APP_NAME}.fly.dev/"

if ! command -v flyctl >/dev/null 2>&1 && ! command -v fly >/dev/null 2>&1; then
  echo "ERRO: Instale flyctl: https://fly.io/docs/flyctl/install/"
  exit 1
fi

FLY="$(command -v flyctl 2>/dev/null || command -v fly)"

echo "==> Login Fly (se necessário)..."
$FLY auth login 2>/dev/null || true

echo "==> Verificando app..."
if ! $FLY apps list 2>/dev/null | grep -q "${APP_NAME}"; then
  echo "==> Criando app ${APP_NAME}..."
  $FLY apps create "${APP_NAME}" --org personal 2>/dev/null || $FLY apps create "${APP_NAME}"
fi

echo "==> Volume persistente (logins/audit)..."
if ! $FLY volumes list -a "${APP_NAME}" 2>/dev/null | grep -q welcome_data; then
  $FLY volumes create welcome_data --region "${REGION}" --size 1 -a "${APP_NAME}" -y
fi

echo "==> Secrets (painel admin)..."
$FLY secrets set \
  PANEL_USER="${PANEL_USER}" \
  PANEL_PASS="${PANEL_PASS}" \
  PANEL_NAME="${PANEL_NAME}" \
  PANEL_RESET_KEY="${PANEL_RESET_KEY}" \
  APP_BASE_URL="https://${APP_NAME}.fly.dev" \
  -a "${APP_NAME}"

echo ""
echo "=============================================="
echo " Setup concluído!"
echo " Painel admin: https://${APP_NAME}.fly.dev/panel/login.php"
echo " Usuário: ${PANEL_USER}"
echo " Senha:   ${PANEL_PASS}"
echo " Reset key: ${PANEL_RESET_KEY}"
echo ""
echo " Próximo passo:"
echo "   bash deploy/fly-deploy.sh"
echo "=============================================="
