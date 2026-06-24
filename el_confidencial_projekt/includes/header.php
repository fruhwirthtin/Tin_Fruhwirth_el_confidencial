<?php
declare(strict_types=1);
$current = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$title = $pageTitle ?? 'El Confidencial';
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Studentski news portal izrađen u HTML-u, CSS-u, PHP-u i MySQL-u.">
    <title><?= e($title) ?> | El Confidencial</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<a class="skip-link" href="#glavni-sadrzaj">Preskoči na sadržaj</a>
<header class="site-header">
    <div class="brand">
        <a href="index.php" aria-label="El Confidencial naslovnica">
            <span class="brand-name">El Confidencial</span>
            <span class="brand-tagline">El diario de los lectores influyentes</span>
        </a>
    </div>
    <nav class="main-nav" aria-label="Glavna navigacija">
        <div class="nav-inner">
            <a href="index.php" <?= $current === 'index.php' ? 'aria-current="page"' : '' ?>>Home</a>
            <a href="kategorija.php?id=europa" <?= $current === 'kategorija.php' && ($_GET['id'] ?? '') === 'europa' ? 'aria-current="page"' : '' ?>>Europa</a>
            <a href="kategorija.php?id=teknautas" <?= $current === 'kategorija.php' && ($_GET['id'] ?? '') === 'teknautas' ? 'aria-current="page"' : '' ?>>Teknautas</a>
            <?php if (is_admin()): ?>
                <a href="unos.php" <?= $current === 'unos.php' ? 'aria-current="page"' : '' ?>>Unos</a>
            <?php endif; ?>
            <a href="administrator.php" <?= $current === 'administrator.php' ? 'aria-current="page"' : '' ?>>Administracija</a>
            <?php if (is_logged_in()): ?>
                <a href="odjava.php">Odjava</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main id="glavni-sadrzaj" class="page-content">
