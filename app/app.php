<?php

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

// Register service providers.
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path' => __DIR__.'/../views',
));


// Register services.
$app['masterflow.masterflow.dao.dataflow'] = $app->share(function ($app) {
    return new Masterflow\Masterflow\DAO\DataflowDAO($app['db']);
});

// Home page
$app->get('/', function () use ($app) {
// 	$dataflows = $app['dao.dataflow']->findAll();
	return $app['twig']->render('index.phtml');
});