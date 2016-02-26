<?php namespace Application;

/**
 * Autoloader
 */
require_once APP_ROOT . '/vendor/autoload.php';

$app = new ImgApplication(APP_ROOT);
$app->run();
