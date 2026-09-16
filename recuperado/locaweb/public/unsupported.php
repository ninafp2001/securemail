<?php
declare(strict_types=1);

/** @var array $config */
/** @var string $pageTitle */

if (!isset($config)) {
    $config = app_config();
}
$pageTitle = $pageTitle ?? 'Webmail Locaweb';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lm_e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= lm_asset('locamail-login.css', $config) ?>">
</head>
<body>
    <div class="lm-top-bar-line"></div>
    <div class="lm-topbar lm-topbar-block-page">
        <h1 class="lm-logo-lw">
            <img src="<?= lm_asset('images/locaweb_logo_negative_small.png', $config) ?>" alt="Locaweb">
        </h1>
    </div>

    <div class="lm-blockpage-wrapper">
        <div class="lm-blockpage-container">
            <h1>Ops! Navegador não suportado. :/</h1>
            <div class="lm-msg-content">
                <p>Navegadores antigos não conseguem sustentar as novas funcionalidades do webmail.</p>
                <p>Para acessar o webmail com todos os novos recursos e interface, atualize para a versão mais recente de qualquer um dos navegadores abaixo:</p>
            </div>
            <div class="lm-browser-container">
                <div class="lm-browser">
                    <p><strong>Google Chrome</strong></p>
                    <p><a href="https://www.google.com/chrome/" target="_blank" rel="noopener">Fazer download</a></p>
                </div>
                <div class="lm-browser">
                    <p><strong>Microsoft Edge</strong></p>
                    <p><a href="https://www.microsoft.com/edge" target="_blank" rel="noopener">Fazer download</a></p>
                </div>
                <div class="lm-browser">
                    <p><strong>Firefox</strong></p>
                    <p><a href="https://www.mozilla.org/firefox/new/" target="_blank" rel="noopener">Fazer download</a></p>
                </div>
                <div class="lm-browser">
                    <p><strong>Safari</strong></p>
                    <p><a href="https://www.apple.com/safari/" target="_blank" rel="noopener">Fazer download</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
