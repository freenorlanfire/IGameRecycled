<?php
require_once __DIR__ . '/partials/config.php';
$pageTitle = 'Contact';
$currentPage = 'contact';
$metaDescription = 'Contact iGameRecycled through a safe local demo form.';

$errors = array();
$submitted = false;
$name = '';
$email = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
    $email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
    $message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'A valid email is required.';
    }

    if ($message === '' || strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters.';
    }

    $submitted = empty($errors);
}

require_once __DIR__ . '/partials/header.php';
?>
<section class="container section-block prose">
    <h1>Contact</h1>
    <p>This demo form validates input locally and confirms submission on-page. It does not send email yet.</p>

    <?php if ($submitted) { ?>
        <div class="notice success" role="status">Thanks, <?php echo ir_escape($name); ?>. Your message has been recorded as a demo submission.</div>
    <?php } elseif (!empty($errors)) { ?>
        <div class="notice error" role="alert">
            <ul>
                <?php foreach ($errors as $error) { ?><li><?php echo ir_escape($error); ?></li><?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="post" action="<?php echo ir_escape(ir_url('contact.php')); ?>" class="contact-form" novalidate>
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?php echo ir_escape($name); ?>" required>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?php echo ir_escape($email); ?>" required>

        <label for="message">Message</label>
        <textarea id="message" name="message" rows="5" required><?php echo ir_escape($message); ?></textarea>

        <button class="btn" type="submit">Submit</button>
    </form>
</section>
<?php require_once __DIR__ . '/partials/footer.php';
