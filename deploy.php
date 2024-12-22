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

set('basis/theme_path', 'content/themes/{{application}}');

set('basis/compiled_files', array(
  '{{basis/theme_path}}/scripts.js',
  '{{basis/theme_path}}/style.css'
));

task('basis:auth:push', function () {
  upload('auth.json', '{{release_path}}/auth.json');
});

task('basis:build', function () {
  runLocally('composer theme setup');
  upload(get('basis/compiled_files'), '{{release_path}}', array('flags' => '-azPR'));
});

before('deploy:vendors', 'basis:auth:push');
after('deploy:vendors', 'basis:build');
?>