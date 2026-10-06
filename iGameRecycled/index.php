<?php
require_once __DIR__ . '/partials/config.php';
$games = ir_game_catalog();
$featured = array_values(array_filter($games, static function ($game) {
    return !empty($game['is_featured']);
}));
$newGames = array_values(array_filter($games, static function ($game) {
    return !empty($game['is_new']);
}));

$pageTitle = 'Home';
$currentPage = 'home';
$metaDescription = 'Play quick browser games in a modern green-tech arcade hub.';
require_once __DIR__ . '/partials/header.php';
?>
<section class="hero-panel">
    <div class="container hero-grid">
        <div>
            <p class="eyebrow">Arcade Portal</p>
            <h1>Play fast. Score high. Repeat.</h1>
            <p>iGameRecycled is a compact arcade portal with instant launch games, modern dark visuals, and responsive controls for mobile and desktop.</p>
            <div class="hero-actions">
                <a class="btn" href="<?php echo ir_escape(ir_url('games.php')); ?>">Browse all games</a>
                <a class="btn btn-outline" href="<?php echo ir_escape(ir_url('guides.php')); ?>">Read quick guides</a>
            </div>
        </div>
        <div class="hero-visual">
            <img src="<?php echo ir_escape(ir_url('img/hero-ir/hero-arcade.svg')); ?>" alt="Arcade hero illustration">
        </div>
    </div>
</section>

<section class="container section-block">
    <div class="section-head">
        <h2>Featured games</h2>
        <a href="<?php echo ir_escape(ir_url('games.php')); ?>">View catalog</a>
    </div>
    <div class="game-grid">
        <?php foreach ($featured as $game) { require __DIR__ . '/partials/game-card.php'; } ?>
    </div>
</section>

<div class="container"><?php $adLabel = 'Homepage Banner Slot'; $adHint = 'Insert your approved ad provider script here.'; require __DIR__ . '/partials/ad-slot.php'; ?></div>

<section class="container section-block">
    <div class="section-head">
        <h2>New and updated</h2>
    </div>
    <div class="game-grid">
        <?php foreach ($newGames as $game) { require __DIR__ . '/partials/game-card.php'; } ?>
    </div>
</section>

<section class="container section-block quick-play">
    <div class="section-head"><h2>Quick play</h2></div>
    <div class="quick-links">
        <?php foreach (array_slice($games, 0, 4) as $game) { ?>
            <a href="<?php echo ir_escape(ir_url('games/' . $game['file'])); ?>"><?php echo ir_escape($game['title']); ?></a>
        <?php } ?>
    </div>
</section>
<?php require_once __DIR__ . '/partials/footer.php';
