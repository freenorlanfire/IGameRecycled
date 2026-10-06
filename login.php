<?php
declare(strict_types=1);

require_once __DIR__ . "/partials/config.php";

if (isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim((string) ($_POST["usuario"] ?? ""));
    $contrasena = (string) ($_POST["contrasena"] ?? "");

    $usuarios = $_SESSION["usuarios"] ?? [];

    if (
        isset($usuarios[$usuario]) &&
        password_verify(
            $contrasena,
            (string) $usuarios[$usuario]["password"]
        )
    ) {
        $_SESSION["usuario"] = $usuario;

        header("Location: index.php");
        exit;
    }

    $error = "Usuario o contraseña incorrectos.";
}

$pageTitle = "Iniciar sesión";
$activePage = "";

require __DIR__ . "/partials/header.php";
?>

<main class="page-container">
    <section class="auth-card">
        <span class="section-kicker">
            MARIO WORLD // ACCESS
        </span>

        <h1>Iniciar sesión</h1>

        <?php if ($error !== ""): ?>
            <div class="form-error">
                <?php echo escapar($error); ?>
            </div>
        <?php endif; ?>

        <form
            class="auth-form"
            method="POST"
            action="login.php"
        >
            <label for="usuario">
                Usuario
            </label>

            <input
                type="text"
                id="usuario"
                name="usuario"
                required
                autocomplete="username"
            >

            <label for="contrasena">
                Contraseña
            </label>

            <input
                type="password"
                id="contrasena"
                name="contrasena"
                required
                autocomplete="current-password"
            >

            <button
                type="submit"
                class="auth-submit"
            >
                Entrar
            </button>
        </form>

        <p class="auth-link">
            ¿No tienes cuenta?
            <a href="register.php">
                Regístrate aquí
            </a>
        </p>
    </section>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>