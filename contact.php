<?php
declare(strict_types=1);

$mensajeEnviado = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mensajeEnviado = true;
}

$pageTitle = "Contact";
$activePage = "contact";

require __DIR__ . "/partials/header.php";
?>

<main class="page-container">
    <section class="content-card">
        <span class="section-kicker">
            CONTACT CENTER
        </span>

        <h1>Contacta con nosotros</h1>

        <?php if ($mensajeEnviado): ?>
            <div class="form-success">
                Mensaje recibido correctamente.
            </div>
        <?php endif; ?>

        <form
            class="contact-form"
            method="POST"
            action="contact.php"
        >
            <label for="nombre">
                Nombre
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                required
            >

            <label for="email">
                Correo electrónico
            </label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <label for="mensaje">
                Mensaje
            </label>

            <textarea
                id="mensaje"
                name="mensaje"
                rows="6"
                required
            ></textarea>

            <button
                type="submit"
                class="auth-submit"
            >
                Enviar mensaje
            </button>
        </form>
    </section>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>