<?php
require_once __DIR__ . '/partials/config.php';
http_response_code(404);
$pageTitle = 'Page Not Found';
$metaDescription = 'The requested iGameRecycled page could not be found.';
require_once __DIR__ . '/partials/header.php';
?>
<section class="container section-block prose">
    <h1>404 - Page Not Found</h1>
    <p>The page you requested does not exist in the iGameRecycled portal.</p>
    <p><a class="btn" href="<?php echo ir_escape(ir_url('index.php')); ?>">Return to homepage</a></p>
</section>
<?php require_once __DIR__ . '/partials/footer.php';
