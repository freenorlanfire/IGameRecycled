<?php
declare(strict_types=1);

require_once __DIR__ . "/partials/config.php";

unset($_SESSION["usuario"]);

header("Location: index.php");
exit;