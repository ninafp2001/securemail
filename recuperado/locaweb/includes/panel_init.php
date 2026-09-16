<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/compat.php';
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';
require_once dirname(__DIR__) . '/includes/security.php';

function admin_panel_name(): string
{
    $config = panel_bootstrap_config();
    return (string) ($config['panel_name'] ?? 'D3V Danadinho');
}

function admin_marquee_message(): string
{
    $config = function_exists('panel_bootstrap_config') ? panel_bootstrap_config() : [];
    return (string) ($config['panel_marquee'] ?? 'Quero alugar um novo apartamento, preciso de 5 mil. Se Deus quiser, vou deixar você rico, D3v Dandinho! 🙏🚀');
}
