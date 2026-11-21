<?php

declare(strict_types=1);

/**
 * SocialNet - Professional Social Media Platform
 * Architecture: Pure PHP MVC
 */

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Core/App.php';

$app = new \App\Core\App(ROOT_PATH);
$app->run();
