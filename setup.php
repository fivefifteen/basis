<?php
// Basis Setup Script

/* Config */

$vars = array(
  'PROJECT_SLUG'              => "project's slug",
  'PROJECT_NAME'              => "project's name",
  'PROJECT_VENDOR_SLUG'       => "vendor/author slug of the project",
  'PROJECT_REPOSITORY'        => "project's git repository URL",
  'MAIL_FROM'                 => "email address to send emails from",
  'MAIL_FROM_NAME'            => "name that emails should be sent from",
  'DATABASE_NAME'             => "name of the local database",
  'DEPLOY_PATH'               => "remote path that the project should be deployed to",
  'CURRENT_PATH'              => "remote public path",
  'STAGING_IP'                => "staging server's IP address",
  'STAGING_USER'              => "staging server's username",
  'STAGING_URL'               => "staging URL",
  'STAGING_DATABASE_HOST'     => "staging server's database host",
  'STAGING_DATABASE_NAME'     => "staging server's database name",
  'STAGING_DATABASE_USER'     => "staging server's database username",
  'STAGING_DATABASE_PASS'     => "staging server's database password",
  'PRODUCTION_IP'             => "production server's IP address",
  'PRODUCTION_USER'           => "production server's username",
  'PRODUCTION_URL'            => "production server's URL",
  'PRODUCTION_DOMAIN'         => "production server's domain",
  'PRODUCTION_DATABASE_HOST'  => "production server's database host",
  'PRODUCTION_DATABASE_NAME'  => "production server's database name",
  'PRODUCTION_DATABASE_USER'  => "production server's database username",
  'PRODUCTION_DATABASE_PASS'  => "production server's database password",
  'GOOGLE_ANALYTICS_ID'       => "production Google Analytics ID",
  'SOPS_AGE_KEY'              => "SOPS AGE Key",
  'SENDGRID_API_KEY'          => "SendGrid API Key",
  'ACF_API_KEY'               => "Advance Cuustom Fields API Key",
  'FIVEFIFTEEN_API_KEY'       => "Five Fifteen Plugins API Key",
  'PHP_VERSION'               => "8.2",
  'COMPOSER_VERSION'          => "2.8.3",
  'MYSQL_VERSION'             => "8.0.40"
);

$var_defaults = array(
  'PROJECT_SLUG'              => 'my-new-website',
  'PROJECT_NAME'              => 'My New Website',
  'PROJECT_VENDOR_SLUG'       => 'my-company',
  'PROJECT_REPOSITORY'        => 'git@github.com:{{BASIS_PROJECT_VENDOR_SLUG}}/{{BASIS_PROJECT_SLUG}}.git',
  'MAIL_FROM_NAME'            => '{{BASIS_PROJECT_SLUG}}',
  'DATABASE_NAME'             => 'wp_{{BASIS_PROJECT_SLUG}}',
  'DEPLOY_PATH'               => '/var/www/public_html',
  'CURRENT_PATH'              => '/var/www/public',
  'STAGING_DATABASE_HOST'     => '127.0.0.1:3306',
  'STAGING_DATABASE_NAME'     => '{{DATABASE_NAME}}',
  'PRODUCTION_DATABASE_HOST'  => '127.0.0.1:3306',
  'PRODUCTION_DATABASE_NAME'  => '{{DATABASE_NAME}}',
  'STAGING_USER'              => 'www-data',
  'PRODUCTION_USER'           => 'www-data'
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

  foreach($vars as $var => $value) {
    $str = str_replace('{{BASIS_' . $var . '}}', $value, $str);
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
  global $var_defaults;

  foreach($vars as $var => &$value) {
    if (!($default = getenv('BASIS_' . $var))) {
      if (isset($var_defaults[$var])) {
        $default = parse_vars($var_defaults[$var]);
      } else {
        $default = '';
      }
    }

    if (!($response = readline("Enter the $value ($default): "))) {
      $response = $default;
    }

    $value = $response;
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
  
  if (!$vars['FIVEFIFTEEN_API_KEY']) {
    $json_files_updated = true;
  
    unset($auth_json_contents['http-basic']['plugins.fivefifteen.com']);
    unset($composer_json_contents['repositories'][1]);
    unset($composer_json_contents['require']['fivefifteen-plugin/tidydash']);
    unset($composer_json_contents['require']['fivefifteen-plugin/whitelist-addon-for-wp-mail-smtp']);
    unset($composer_json_contents['require']['fivefifteen-vendor/gravityforms']);
  }
  
  if (!$vars['ACF_API_KEY']) {
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