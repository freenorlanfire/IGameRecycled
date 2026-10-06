<?php
declare(strict_types=1);

$pageTitle = "About";
$activePage = "about";

require __DIR__ . "/partials/header.php";
?>

<main class="page-container">
    <section class="content-card">
        <span class="section-kicker">
            ABOUT THE PROJECT
        </span>

        <h1>Sobre Mario World Arcade</h1>

        <p>
            Mario World Arcade es una plataforma experimental de juegos
            web desarrollada con PHP, HTML5 Canvas, CSS y JavaScript.
        </p>

        <p>
            El objetivo del proyecto es aprender programación dinámica,
            sesiones PHP y desarrollo de videojuegos interactivos.
        </p>

        <div class="tech-list">
            <span>PHP</span>
            <span>JavaScript</span>
            <span>HTML5 Canvas</span>
            <span>CSS3</span>
            <span>Responsive Design</span>
        </div>
    </section>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>