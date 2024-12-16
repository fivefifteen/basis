<?php
// Basis Setup Script

/* Config */

$vars = array(
  'PROJECT_SLUG'              => array("project's slug", "my-new-website"),
  'PROJECT_NAME'              => array("project's name", "My New Website"),
  'PROJECT_VENDOR_SLUG'       => array("vendor/author slug of the project", "my-company"),
  'PROJECT_REPOSITORY'        => array("project's git repository URL", "git@github.com:{{BASIS_PROJECT_VENDOR_SLUG}}/{{BASIS_PROJECT_SLUG}}.git"),
  'MAIL_FROM'                 => array("email address to send emails from", "my-email@{{BASIS_PROJECT_SLUG}}.com"),
  'MAIL_FROM_NAME'            => array("name that emails should be sent from", "{{BASIS_PROJECT_NAME}}"),
  'DATABASE_NAME'             => array("name of the local database", "wp_{{BASIS_PROJECT_SLUG}}"),
  'DEPLOY_PATH'               => array("remote path that the project should be deployed to", "/var/www/public_html"),
  'CURRENT_PATH'              => array("remote public path", "/var/www/public"),
  'STAGING_IP'                => array("staging server's IP address", '123.456.789.100'),
  'STAGING_USER'              => array("staging server's username", "www-data"),
  'STAGING_URL'               => array("staging URL", "https://staging.{{BASIS_PROJECT_SLUG}}.com"),
  'STAGING_DATABASE_HOST'     => array("staging server's database host", "127.0.0.1:3306"),
  'STAGING_DATABASE_NAME'     => array("staging server's database name", "{{BASIS_DATABASE_NAME}}"),
  'STAGING_DATABASE_USER'     => array("staging server's database username", "user"),
  'STAGING_DATABASE_PASS'     => array("staging server's database password", "pass"),
  'PRODUCTION_IP'             => array("production server's IP address", "123.456.789.100"),
  'PRODUCTION_USER'           => array("production server's username", "www-data"),
  'PRODUCTION_URL'            => array("production server's URL", "https://{{BASIS_PROJECT_SLUG}}.com"),
  'PRODUCTION_DOMAIN'         => array("production server's domain", "{{BASIS_PROJECT_SLUG}}.com"),
  'PRODUCTION_DATABASE_HOST'  => array("production server's database host", "127.0.0.1:3306"),
  'PRODUCTION_DATABASE_NAME'  => array("production server's database name", "{{BASIS_DATABASE_NAME}}"),
  'PRODUCTION_DATABASE_USER'  => array("production server's database username", "user"),
  'PRODUCTION_DATABASE_PASS'  => array("production server's database password", "pass"),
  'GOOGLE_ANALYTICS_ID'       => array("production Google Analytics ID", ""),
  'SOPS_AGE_KEY'              => array("SOPS AGE Key", ""),
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
  'readme.template.md'
);

$file_delete_list = array(
  'assets',
  'composer.json',
  'composer.lock',
  'license.md',
  'readme.md',
  'setup.php',
  'content/themes/primer/.git',
  'content/themes/primer/license.md',
);

$file_rename_list = array(
  'auth.template.json'      => 'auth.json',
  'composer.template.json'  => 'composer.json',
  'deploy.template.yml'     => 'deploy.yml',
  'readme.template.md'      => 'readme.md',
  'content/themes/primer'   => '{{BASIS_PROJECT_SLUG}}'
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

function write_json($file, $arr) {
  write("Updating {$file}...");
  return file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
    write_json('auth.json', $auth_json_contents);
    write_json('composer.json', $composer_json_contents);
  }
}


/* Logo */

write(' ____            _     ');
write('|  _ \          (_)     ');
write('| |_) | __ _ ___ _ ___  ');
write('|  _ < / _` / __| / __| ');
write('| |_) | (_| \__ \ \__ \ ');
write('|____/ \__,_|___/_|___/ ');

/* Perform Actions */

do_var_prompts();
do_file_processing();
do_file_deletion();
do_file_renaming();
do_json_updating();

write('Done!');

system('composer install');
?>