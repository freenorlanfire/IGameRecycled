<?php
declare(strict_types=1);

require_once __DIR__ . "/partials/config.php";

if (isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim((string) ($_POST["usuario"] ?? ""));
    $contrasena = (string) ($_POST["contrasena"] ?? "");
    $confirmacion = (string) ($_POST["confirmacion"] ?? "");

    if (strlen($usuario) < 3) {
        $error = "El usuario debe tener al menos 3 caracteres.";
    } elseif (strlen($contrasena) < 4) {
        $error = "La contraseña debe tener al menos 4 caracteres.";
    } elseif ($contrasena !== $confirmacion) {
        $error = "Las contraseñas no coinciden.";
    } else {
        if (!isset($_SESSION["usuarios"])) {
            $_SESSION["usuarios"] = [];
        }

        if (isset($_SESSION["usuarios"][$usuario])) {
            $error = "Ese usuario ya está registrado.";
        } else {
            $_SESSION["usuarios"][$usuario] = [
                "password" => password_hash(
                    $contrasena,
                    PASSWORD_DEFAULT
                )
            ];

            $success = "Cuenta creada. Ya puedes iniciar sesión.";
        }
    }
}

$pageTitle = "Registrar cuenta";
$activePage = "";

require __DIR__ . "/partials/header.php";
?>

<main class="page-container">
    <section class="auth-card">
        <span class="section-kicker">
            MARIO WORLD // REGISTER
        </span>

        <h1>Crear cuenta</h1>

        <?php if ($error !== ""): ?>
            <div class="form-error">
                <?php echo escapar($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="form-success">
                <?php echo escapar($success); ?>
            </div>
        <?php endif; ?>

        <form
            class="auth-form"
            method="POST"
            action="register.php"
        >
            <label for="usuario">
                Usuario
            </label>

            <input
                type="text"
                id="usuario"
                name="usuario"
                required
                minlength="3"
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
                minlength="4"
                autocomplete="new-password"
            >

            <label for="confirmacion">
                Repite la contraseña
            </label>

            <input
                type="password"
                id="confirmacion"
                name="confirmacion"
                required
                minlength="4"
                autocomplete="new-password"
            >

            <button
                type="submit"
                class="auth-submit"
            >
                Registrar cuenta
            </button>
        </form>

        <p class="auth-link">
            ¿Ya tienes una cuenta?
            <a href="login.php">
                Inicia sesión
            </a>
        </p>
    </section>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>