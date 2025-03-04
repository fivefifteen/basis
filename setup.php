<?php
// Basis Setup Script

/* Config */

$vars = array(
  'PROJECT_SLUG'              => array("project's slug", basename(dirname(__FILE__))),
  'PROJECT_NAME'              => array("project's name", "My New WordPress Website"),
  'PROJECT_VENDOR_SLUG'       => array("vendor/author slug of the project", "my-company"),
  'PROJECT_REPOSITORY'        => array("project's git repository URL", "git@github.com:{{BASIS_PROJECT_VENDOR_SLUG}}/{{BASIS_PROJECT_SLUG}}.git"),
  'DATABASE_NAME'             => array("name of the local database", "wp_{{BASIS_PROJECT_SLUG}}"),
  'DEPLOY_PATH'               => array("remote path that the project should be deployed to", "/var/www/public_html"),
  'CURRENT_PATH'              => array("remote public path", "/var/www/public"),
  'STAGING_BRANCH'            => array("staging branch", "development"),
  'STAGING_IP'                => array("staging server's IP address", '123.456.789.100'),
  'STAGING_USER'              => array("staging server's username", "www-data"),
  'STAGING_URL'               => array("staging URL", "https://staging.{{BASIS_PROJECT_SLUG}}.com"),
  'STAGING_DATABASE_HOST'     => array("staging server's database host", "127.0.0.1:3306"),
  'STAGING_DATABASE_NAME'     => array("staging server's database name", "{{BASIS_DATABASE_NAME}}"),
  'STAGING_DATABASE_USER'     => array("staging server's database username", "user"),
  'STAGING_DATABASE_PASS'     => array("staging server's database password", "pass"),
  'PRODUCTION_BRANCH'         => array("production branch", "main"),
  'PRODUCTION_IP'             => array("production server's IP address", "123.456.789.100"),
  'PRODUCTION_USER'           => array("production server's username", "www-data"),
  'PRODUCTION_URL'            => array("production server's URL", "https://{{BASIS_PROJECT_SLUG}}.com"),
  'PRODUCTION_DOMAIN'         => array("production server's domain", "{{BASIS_PROJECT_SLUG}}.com"),
  'PRODUCTION_DATABASE_HOST'  => array("production server's database host", "127.0.0.1:3306"),
  'PRODUCTION_DATABASE_NAME'  => array("production server's database name", "{{BASIS_DATABASE_NAME}}"),
  'PRODUCTION_DATABASE_USER'  => array("production server's database username", "user"),
  'PRODUCTION_DATABASE_PASS'  => array("production server's database password", "pass"),
  'MAIL_FROM'                 => array("email address to send emails from", "info@{{BASIS_PRODUCTION_DOMAIN}}"),
  'MAIL_FROM_NAME'            => array("name that emails should be sent from", "{{BASIS_PROJECT_NAME}}"),
  'GOOGLE_ANALYTICS_ID'       => array("production Google Analytics ID", ""),
  'SOPS_AGE_PUBLIC_KEY'       => array("SOPS AGE Public Key", ""),
  'SENDGRID_API_KEY'          => array("SendGrid API Key", ""),
  'ACF_API_KEY'               => array("Advance Cuustom Fields API Key", ""),
  'FIVEFIFTEEN_API_KEY'       => array("Five Fifteen Plugins API Key", ""),
  'PHP_VERSION'               => array("desired PHP version", "8.2"),
  'COMPOSER_VERSION'          => array("desired Composer version", "2.8.3"),
  'MYSQL_VERSION'             => array("desired MySql version", "8.0.40")
);

$file_process_list = array(
  '.lando.yml',
  '.sops.yaml',
  'auth.template.json',
  'composer.template.json',
  'deploy.template.yml',
  'readme.template.md',
  'reloader.php',
);

$file_delete_list = array(
  'assets',
  'composer.json',
  'composer.lock',
  'license.md',
  'readme.md',
  'setup.php',
  'content/themes/{{BASIS_PROJECT_SLUG}}/license.md',
);

$file_rename_list = array(
  'auth.template.json'      => 'auth.json',
  'composer.template.json'  => 'composer.json',
  'deploy.template.yml'     => 'deploy.yml',
  'readme.template.md'      => 'readme.md'
);


/* Safety-Net */

if (basename(dirname(__FILE__)) === 'basis') {
  echo "\nThis script should only be ran on a new copy of Basis.\n\n";
  exit;
}


/* Helpers */

function get_json($file) {
  write("Loading {$file}...");
  return json_decode(file_get_contents($file), true);
}

function parse_vars($str) {
  global $vars;

  foreach($vars as $key => $var) {
    $str = str_replace('{{BASIS_' . $key . '}}', $var[1], $str);
  }

  return $str;
}

function remove_directory($path) {
  $files = glob(preg_replace('/(\*|\?|\[)/', '[$1]', $path) . '/{,.}*', GLOB_BRACE);

  foreach ($files as $file) {
    if ($file == $path . '/.' || $file == $path . '/..') continue;
    is_dir($file) ? remove_directory($file) : unlink($file);
  }

  rmdir($path);
}

function write($str) {
  echo $str . "\n";
}

function write_json($file, $arr, $force_object = false) {
  write("Updating {$file}...");
  $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | ($force_object ? JSON_FORCE_OBJECT : null);
  return file_put_contents($file, json_encode($arr, $flags));
}


/* Actions */

function do_var_prompts() {
  global $vars;

  foreach($vars as $key => &$var) {
    $default = parse_vars(getenv('BASIS_' . $key) ?: $var[1]);

    if (!($response = readline("Enter the {$var[0]} ($default): "))) {
      $response = $default;
    }

    $var[1] = $response;
  }
}

function do_file_processing() {
  global $file_process_list;

  foreach($file_process_list as $file_path) {
    if (file_exists($file_path)) {
      write("Processing $file_path...");

      try {
        $file_contents = parse_vars(file_get_contents($file_path));
        file_put_contents($file_path, $file_contents);
      } catch (Exception $e) {
        write("Error: " . $e->getMessage());
      }
    } else {
      write("Error: Could not find $file_path");
    }
  }
}

function do_file_deletion() {
  global $file_delete_list;

  foreach($file_delete_list as $file_path) {
    $file_path = parse_vars($file_path);

    if (file_exists($file_path)) {
      write("Deleting $file_path...");

      try {
        if (is_dir($file_path)) {
          remove_directory($file_path);
        } else {
          unlink($file_path);
        }
      } catch (Exception $e) {
        write("Error: " . $e->getMessage());
      }
    } else {
      write("Error: Could not find $file_path");
    }
  }
}

function do_file_renaming() {
  global $file_rename_list;

  foreach($file_rename_list as $file_path => $new_name) {
    if (file_exists($file_path)) {
      $new_name = parse_vars($new_name);

      write("Renaming $file_path to $new_name...");

      try {
        rename($file_path, $new_name);
      } catch (Exception $e) {
        write("Error: " . $e->getMessage());
      }
    } else {
      write("Error: Could not find $file_path");
    }
  }
}

function do_json_updating() {
  global $vars;

  $auth_json_contents = get_json('auth.json');
  $composer_json_contents = get_json('composer.json');
  $json_files_updated = false;

  if (!$vars['FIVEFIFTEEN_API_KEY'][1]) {
    $json_files_updated = true;

    unset($auth_json_contents['http-basic']['plugins.fivefifteen.com']);
    unset($composer_json_contents['repositories'][1]);
    unset($composer_json_contents['require']['fivefifteen-plugin/flexible-content-modules']);
    unset($composer_json_contents['require']['fivefifteen-plugin/guidebook']);
    unset($composer_json_contents['require']['fivefifteen-plugin/tidydash']);
    unset($composer_json_contents['require']['fivefifteen-plugin/whitelist-addon-for-wp-mail-smtp']);
    unset($composer_json_contents['require']['fivefifteen-vendor/gravityforms']);
  }

  if (!$vars['ACF_API_KEY'][1]) {
    $json_files_updated = true;

    unset($auth_json_contents['http-basic']['connect.advancedcustomfields.com']);
    unset($composer_json_contents['repositories'][2]);
    unset($composer_json_contents['require']['wpengine/advanced-custom-fields-pro']);
  }

  if ($json_files_updated) {
    write_json('auth.json', $auth_json_contents, true);
    write_json('composer.json', $composer_json_contents);
  }
}

function do_theme_data_updating() {
  $theme_name = parse_vars(getenv('BASIS_THEME_DATA_NAME') ?: '');
  $theme_description = parse_vars(getenv('BASIS_THEME_DATA_DESCRIPTION') ?: '');
  $theme_author = parse_vars(getenv('BASIS_THEME_DATA_AUTHOR') ?: '');
  $theme_author_uri = parse_vars(getenv('BASIS_THEME_DATA_AUTHOR_URI') ?: '');
  $theme_stylesheet = parse_vars(getenv('BASIS_THEME_DATA_STYLESHEET') ?: 'content/themes/{{BASIS_PROJECT_SLUG}}/scss/style.scss');

  if ($theme_stylesheet && ($theme_name || $theme_description) && file_exists($theme_stylesheet)) {
    $theme_data = file_get_contents($theme_stylesheet, false, null, 0, 8 * 1024);

    if ($theme_name) {
      $theme_data = preg_replace('/(Theme Name:\s*)(.*)/', "$1{$theme_name}", $theme_data);
    }

    if ($theme_description) {
      $theme_data = preg_replace('/(Description:\s*)(.*)/', "$1{$theme_description}", $theme_data);
    }

    if ($theme_author) {
      $theme_data = preg_replace('/(Author:\s*)(.*)/', "$1{$theme_author}", $theme_data);
    }

    if ($theme_author_uri) {
      $theme_data = preg_replace('/(Author URI:\s*)(.*)/', "$1{$theme_author_uri}", $theme_data);
    }

    write("Updating theme data in {$theme_stylesheet}...");
    file_put_contents($theme_stylesheet, $theme_data);
  }
}


/* Logo */

write(' ____            _      ');
write('|  _ \          (_)     ');
write('| |_) | __ _ ___ _ ___  ');
write('|  _ < / _` / __| / __| ');
write('| |_) | (_| \__ \ \__ \ ');
write('|____/ \__,_|___/_|___/ ');
write('');

/* Perform Actions */

do_var_prompts();

$theme_install_cmd = getenv('BASIS_THEME_INSTALL_CMD');
$post_theme_install_cmd = getenv('BASIS_POST_THEME_INSTALL_CMD');
$primer_version = getenv('BASIS_PRIMER_VERSION') ?: null;
$using_primer = !$theme_install_cmd || $primer_version;

if (!$theme_install_cmd) {
  if ($primer_version) $primer_version = " \"{$primer_version}\"";
  $theme_install_cmd = "composer create-project fivefifteen/primer content/themes/{{BASIS_PROJECT_SLUG}}{$primer_version} --no-install";
}

write('Installing theme...');
system(parse_vars($theme_install_cmd));

if ($using_primer) {
  do_theme_data_updating();

  if ($vars['FIVEFIFTEEN_API_KEY'][1]) {
    $flex_modules_path = parse_vars('content/themes/{{BASIS_PROJECT_SLUG}}/flex-modules');
    write("Creating {$flex_modules_path}...");
    mkdir($flex_modules_path, 0755);
    touch("{$flex_modules_path}/.gitkeep");
  }

  if ($vars['ACF_API_KEY'][1]) {
    $acf_json_path = parse_vars('content/themes/{{BASIS_PROJECT_SLUG}}/acf-json');
    write("Creating {$acf_json_path}...");
    mkdir($acf_json_path, 0755);
    touch("{$acf_json_path}/.gitkeep");
  }
}

if ($post_theme_install_cmd) {
  write('Running post theme install command...');
  system(parse_vars($post_theme_install_cmd));
}

do_file_processing();
do_file_deletion();
do_file_renaming();
do_json_updating();

write('Done!');

if (!getenv('BASIS_NO_GIT_INIT')) {
  system('git init');
}

if ($post_setup_cmd = getenv('BASIS_POST_SETUP_CMD')) {
  write('Running post setup command...');
  system(parse_vars($post_setup_cmd));
}

if (!getenv('BASIS_NO_START')) {
  system('lando start');
}

if ($post_start_cmd = getenv('BASIS_POST_START_CMD')) {
  write('Running post start command...');
  system(parse_vars($post_start_cmd));
}
?>