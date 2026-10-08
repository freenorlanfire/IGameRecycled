<?php
require_once __DIR__ . '/partials/config.php';
$games = ir_game_catalog();
$categories = ir_categories();
$pageTitle = 'Categories';
$currentPage = 'categories';
$metaDescription = 'Explore iGameRecycled games by genre and play style.';
require_once __DIR__ . '/partials/header.php';
?>
<section class="container section-block">
    <div class="section-head"><h1>Browse by Category</h1></div>
    <div class="chip-row category-links">
        <?php foreach ($categories as $category) { ?>
            <a class="chip" href="<?php echo ir_escape(ir_url('games.php?category=' . urlencode(strtolower($category)))); ?>"><?php echo ir_escape($category); ?></a>
        <?php } ?>
    </div>
</section>

<section class="container section-block">
    <div class="game-grid" data-catalog-grid>
        <?php foreach ($games as $game) { require __DIR__ . '/partials/game-card.php'; } ?>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php';
