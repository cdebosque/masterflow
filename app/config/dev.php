<?php

// include the prod configuration
require __DIR__.'/prod.php';

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'charset'  => 'utf8',
    'host'     => '127.0.0.1',  // Mandatory for PHPUnit testing
    'port'     => '3306',
    'dbname'   => 'masterflow',
    'user'     => 'root',
    'password' => '',
);

// enable the debug mode
$app['debug'] = true;

// define log parameters
$app['monolog.level'] = 'INFO';

// Configuration du mailer
$app['swiftmailer.options'] = array(
    'host'      => 'smtp.gmail.com',
    'port'      => '465',
    'username'  => 'XXXXXXX@gmail.com',
    'password'  => 'YYYYYYYYYY',
    'encryption'=> 'ssl',
    'auth_mode' => 'login',
);