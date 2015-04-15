<?php
// Autoload du dossier vendor
require_once __DIR__.'/../vendor/autoload.php';

// Chargement d'une surcharge de l'application pour la sécurisation des routes ( @see : http://silex.sensiolabs.org/doc/usage.html#traits )
$app = new Masterflow\Config\NexecomApplication();

// Chargement d'une surcharge de la gestion des routes pour la sécurisation ( @see : http://silex.sensiolabs.org/doc/usage.html#traits )
$app['route_class'] = 'Masterflow\Config\NexecomRoute';

// Chargement de la configuration liée à l'environnement 
require __DIR__.'/../app/config/dev.php';

// Configuratuin de l'application 
require __DIR__.'/../app/app.php';

// Récupération des routes
require __DIR__.'/../app/routes.php';

$app->run();