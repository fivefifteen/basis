<?php
$private_dependencies = array(
  'fivefifteen-plugin/tidydash',
  'fivefifteen-plugin/whitelist-addon-for-wp-mail-smtp',
  'fivefifteen-vendor/gravityforms',
  'wpengine/advanced-custom-fields-pro'
);

$composer_json = json_decode(file_get_contents('composer.json'), true);

foreach($private_dependencies as $private_dependency) {
  unset($composer_json['require'][$private_dependency]);
}

file_put_contents('composer.json', json_encode($composer_json, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES));
?>