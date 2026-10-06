<?php
declare(strict_types=1);

$pageTitle = "Community";
$activePage = "community";

require __DIR__ . "/partials/header.php";
?>

<main class="page-container">
    <section class="content-card">
        <span class="section-kicker">
            COMMUNITY HUB
        </span>

        <h1>La aventura continúa</h1>

        <p>
            Bienvenido a la comunidad de Mario World Arcade.
            Aquí podrás compartir récords, ideas y nuevos juegos.
        </p>

        <div class="feature-grid">
            <article class="feature-card">
                <span>🏆</span>
                <h2>Récords</h2>
                <p>Supera tus mejores puntuaciones.</p>
            </article>

            <article class="feature-card">
                <span>🎮</span>
                <h2>Jugadores</h2>
                <p>Descubre nuevas experiencias arcade.</p>
            </article>

            <article class="feature-card">
                <span>💡</span>
                <h2>Ideas</h2>
                <p>Propón nuevos juegos para la plataforma.</p>
            </article>
        </div>
    </section>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>