<?php
require_once('./vendor/autoload.php');
require_once('recipe/wordup.php');

use function \Deployer\{
  after,
  before,
  get,
  import,
  runLocally,
  set,
  task,
  upload
};

set('basis/theme_path', 'content/themes/{{BASIS_THEME_NAME}}');

set('basis/compiled_files', array(
  '{{basis/theme_path}}/js/scripts.min.js',
  '{{basis/theme_path}}/admin.css',
  '{{basis/theme_path}}/style.css'
));

task('basis:auth:push', function () {
  upload('auth.json', '{{release_path}}/auth.json');
});

task('basis:build', function () {
  runLocally('composer run fetch');
  runLocally('composer run build');
  upload(get('basis/compiled_files'), '{{release_path}}', array('flags' => '-azPR'));
});

before('deploy:vendors', 'basis:auth:push');
after('deploy:vendors', 'basis:build');
?>