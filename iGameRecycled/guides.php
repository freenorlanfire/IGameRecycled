<?php
require_once __DIR__ . '/partials/config.php';
$pageTitle = 'Guides';
$currentPage = 'guides';
$metaDescription = 'Short strategy guides for improving your arcade scores.';
require_once __DIR__ . '/partials/header.php';
?>
<section class="container section-block prose">
    <h1>Quick Guides</h1>
    <h2>Neon Archer</h2>
    <p>Focus on rhythm over speed. Landing steady center hits builds consistency and stronger streaks.</p>
    <h2>Nitro Highway Series</h2>
    <p>Stay near lane center and make short movements. Oversteering is the fastest way to lose control in dense traffic.</p>
    <h2>Flappy Modes</h2>
    <p>Tap lightly in a repeatable cadence. In V2, prioritize survival and avoid risky pipe gaps early.</p>
    <h2>Snake and Spaceman</h2>
    <p>Use edge lanes as reset zones, and always plan one move ahead before collecting score items.</p>
</section>
<?php require_once __DIR__ . '/partials/footer.php';
