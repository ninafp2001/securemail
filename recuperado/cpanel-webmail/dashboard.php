<?php
declare(strict_types=1);

session_start();

if (empty($_SESSION['webmail_user'])) {
    header('Location: index.php');
    exit;
}

$user = htmlspecialchars((string) $_SESSION['webmail_user'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Webmail</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Open Sans", sans-serif; margin: 40px; color: #333; }
        a { color: #ff6c2c; }
    </style>
</head>
<body>
    <h1>Webmail</h1>
    <p>Logado como: <strong><?= $user ?></strong></p>
    <p><a href="index.php?logout=1">Sair</a></p>
</body>
</html>
