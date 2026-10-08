<?php
$pageTitle = isset($pageTitle) ? (string) $pageTitle : 'iGameRecycled';
$currentPage = isset($currentPage) ? (string) $currentPage : '';
$metaDescription = isset($metaDescription) ? (string) $metaDescription : 'iGameRecycled arcade portal with quick-play browser games.';
$portalBase = ir_portal_base_path();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo ir_escape($metaDescription); ?>">
    <title><?php echo ir_escape($pageTitle); ?> | iGameRecycled</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo ir_escape(ir_url('img/svg/logo-igrecycled.svg')); ?>">
    <link rel="stylesheet" href="<?php echo ir_escape(ir_url('css/app.css')); ?>">
    <link rel="stylesheet" href="<?php echo ir_escape(ir_url('css/games.css')); ?>">
    <link rel="stylesheet" href="<?php echo ir_escape(ir_url('css/responsive.css')); ?>">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8917055252143931"
     crossorigin="anonymous"></script>
</head>
<body>
<div class="site-shell">
    <header class="site-header" id="top">
        <div class="container nav-row">
            <a class="brand" href="<?php echo ir_escape(ir_url('index.php')); ?>" aria-label="Go to homepage">
                <img src="<?php echo ir_escape(ir_url('img/svg/logo-igrecycled.svg')); ?>" alt="iGameRecycled logo">
                <span>iGameRecycled</span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-menu" data-menu-toggle>
                <span></span><span></span><span></span>
                <span class="sr-only">Open menu</span>
            </button>
            <nav class="site-nav" id="site-menu" data-menu>
                <a href="<?php echo ir_escape(ir_url('index.php')); ?>"<?php echo $currentPage === 'home' ? ' class="active"' : ''; ?>>Home</a>
                <a href="<?php echo ir_escape(ir_url('games.php')); ?>"<?php echo $currentPage === 'games' ? ' class="active"' : ''; ?>>Games</a>
                <a href="<?php echo ir_escape(ir_url('categories.php')); ?>"<?php echo $currentPage === 'categories' ? ' class="active"' : ''; ?>>Categories</a>
                <a href="<?php echo ir_escape(ir_url('guides.php')); ?>"<?php echo $currentPage === 'guides' ? ' class="active"' : ''; ?>>Guides</a>
                <a href="<?php echo ir_escape(ir_url('about.php')); ?>"<?php echo $currentPage === 'about' ? ' class="active"' : ''; ?>>About</a>
                <a href="<?php echo ir_escape(ir_url('contact.php')); ?>"<?php echo $currentPage === 'contact' ? ' class="active"' : ''; ?>>Contact</a>
            </nav>
        </div>
    </header>
    <main class="site-main">
