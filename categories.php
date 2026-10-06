<?php
declare(strict_types=1);

require_once __DIR__ . "/partials/config.php";

$pageTitle = "Categorías";
$activePage = "categories";

require __DIR__ . "/partials/header.php";
?>

<main class="page-container">
    <section class="content-card">
        <span class="section-kicker">
            GAME CATEGORIES
        </span>

        <h1>Explora los juegos</h1>

        <div class="category-grid">
            <?php foreach ($juegos as $juego): ?>
                <a
                    class="category-card"
                    href="games/<?php echo rawurlencode(
                        $juego["archivo"]
                    ); ?>"
                >
                    <span>
                        <?php echo escapar($juego["icono"]); ?>
                    </span>

                    <strong>
                        <?php echo escapar($juego["nombre"]); ?>
                    </strong>

                    <small>
                        <?php echo escapar($juego["categoria"]); ?>
                    </small>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>