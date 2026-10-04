<?php
declare(strict_types=1);
$page_title = $page_title ?? 'Learn classic cryptography';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="An interactive, educational cryptography lab for four classic ciphers.">
    <title><?= e($page_title) ?> · CipherLab</title>
    <link rel="stylesheet" href="<?= isset($_SESSION['user']) ? '../assets/css/style.css' : 'assets/css/style.css' ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= isset($_SESSION['user']) ? 'dashboard.php' : 'index.php' ?>" aria-label="CipherLab home"><span class="brand-symbol">C</span><span>Cipher<span class="brand-accent">Lab</span></span></a>
    <nav class="site-nav" aria-label="Main navigation">
        <?php if (isset($_SESSION['user'])): ?>
            <a href="<?= str_contains($_SERVER['SCRIPT_NAME'], '/ciphers/') ? '../dashboard.php' : 'dashboard.php' ?>">Dashboard</a>
            <span class="nav-user"><?= e($_SESSION['user']['name']) ?></span>
            <form method="post" action="<?= str_contains($_SERVER['SCRIPT_NAME'], '/ciphers/') ? '../logout.php' : 'logout.php' ?>">
                <?= csrf_field() ?>
                <button class="button button-quiet button-small" type="submit">Log out</button>
            </form>
        <?php else: ?>
            <a href="index.php">Log in</a>
            <a class="button button-primary button-small" href="register.php">Create account</a>
        <?php endif; ?>
    </nav>
</header>
