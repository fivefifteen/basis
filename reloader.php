<?php
require_once('./vendor/autoload.php');
require_once('./content/themes/{{BASIS_PROJECT_SLUG}}/vendor/autoload.php');
new \Piler\Reloader('./content/themes/{{BASIS_PROJECT_SLUG}}/composer.json');
?>