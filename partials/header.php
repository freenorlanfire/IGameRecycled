<?php
declare(strict_types=1);

require_once __DIR__ . "/config.php";

$pageTitle = $pageTitle ?? "Mario World Arcade";
$activePage = $activePage ?? "";

$usuarioConectado = isset($_SESSION["usuario"]);
$nombreUsuario = $usuarioConectado
    ? (string) $_SESSION["usuario"]
    : "";
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?php echo escapar($pageTitle); ?></title>

    <link
        rel="stylesheet"
        href="css/estilos.css"
    >

    <link
        rel="stylesheet"
        href="css/miniaturas_launcher.css"
    >
</head>

<body>
    <header class="site-header">
        <div class="site-header-inner">
            <a
                class="brand-logo"
                href="index.php"
                aria-label="Ir al inicio"
            >
                <span class="brand-mark">M</span>

                <span class="brand-copy">
                    <strong>MARIO</strong>
                    <small>WORLD ARCADE</small>
                </span>
            </a>

            <button
                type="button"
                class="menu-toggle"
                id="menuToggle"
                aria-label="Abrir menú"
                aria-expanded="false"
                aria-controls="mainNavigation"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav
                class="main-navigation"
                id="mainNavigation"
                aria-label="Navegación principal"
            >
                <a
                    class="nav-link <?php echo $activePage === "home"
                        ? "active"
                        : ""; ?>"
                    href="index.php"
                >
                    <span class="nav-icon">⌂</span>
                    Home
                </a>

                <a
                    class="nav-link <?php echo $activePage === "community"
                        ? "active"
                        : ""; ?>"
                    href="community.php"
                >
                    <span class="nav-icon">◉</span>
                    Community
                </a>

                <a
                    class="nav-link <?php echo $activePage === "contact"
                        ? "active"
                        : ""; ?>"
                    href="contact.php"
                >
                    <span class="nav-icon">✉</span>
                    Contact
                </a>

                <a
                    class="nav-link <?php echo $activePage === "categories"
                        ? "active"
                        : ""; ?>"
                    href="categories.php"
                >
                    <span class="nav-icon">▦</span>
                    Categorías
                </a>

                <a
                    class="nav-link <?php echo $activePage === "about"
                        ? "active"
                        : ""; ?>"
                    href="about.php"
                >
                    <span class="nav-icon">ⓘ</span>
                    About
                </a>

                <?php if ($usuarioConectado): ?>
                    <span class="user-badge">
                        Hola,
                        <?php echo escapar($nombreUsuario); ?>
                    </span>

                    <a
                        class="nav-auth nav-auth-secondary"
                        href="logout.php"
                    >
                        Salir
                    </a>
                <?php else: ?>
                    <a
                        class="nav-auth"
                        href="login.php"
                    >
                        Iniciar sesión
                    </a>

                    <a
                        class="nav-auth nav-auth-primary"
                        href="register.php"
                    >
                        Registrar
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>