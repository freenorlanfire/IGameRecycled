<?php
require_once __DIR__ . '/partials/config.php';
$games = ir_game_catalog();
$categories = ir_categories();
$pageTitle = 'Games';
$currentPage = 'games';
$metaDescription = 'Full iGameRecycled game catalog with quick search and category filters.';
require_once __DIR__ . '/partials/header.php';
?>
<section class="container section-block">
    <div class="section-head"><h1>Game Catalog</h1></div>
    <div class="catalog-tools" data-catalog-controls>
        <input type="search" id="game-search" placeholder="Search by title or tag" autocomplete="off" data-game-search>
        <div class="chip-row" data-category-filters>
            <button class="chip active" type="button" data-filter="all">All</button>
            <?php foreach ($categories as $category) { ?>
                <button class="chip" type="button" data-filter="<?php echo ir_escape(strtolower($category)); ?>"><?php echo ir_escape($category); ?></button>
            <?php } ?>
        </div>
    </div>
    <div class="game-grid" data-catalog-grid>
        <?php foreach ($games as $game) { require __DIR__ . '/partials/game-card.php'; } ?>
    </div>
    <p class="empty-state" data-empty-state hidden>No games match your current filters.</p>
</section>
<div class="container"><?php $adLabel = 'Catalog Sidebar Slot'; $adHint = 'Use this area for compliant ad units.'; require __DIR__ . '/partials/ad-slot.php'; ?></div>
<?php require_once __DIR__ . '/partials/footer.php';
