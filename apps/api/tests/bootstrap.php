<?php

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('DB_URL=');
putenv('MESSAGING_DRIVER=off');
putenv('MESSAGING_SYNC=true');
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['DB_URL'] = '';
$_ENV['MESSAGING_DRIVER'] = 'off';
$_ENV['MESSAGING_SYNC'] = 'true';
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = ':memory:';
$_SERVER['DB_URL'] = '';
$_SERVER['MESSAGING_DRIVER'] = 'off';
$_SERVER['MESSAGING_SYNC'] = 'true';

require __DIR__.'/../vendor/autoload.php';
