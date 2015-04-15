<?php

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Silex\Provider;

// Secure routes 
use Silex\Route\SecurityTrait;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        // Définit que tout le monde peut accéder à la page de login
        'login' => array(
            'pattern' => '^/user/login$',
        ),
        // http://localhost/silex/web/user/forgot-password
        'forgot-password' => array(
            'pattern' => '^/user/forgot-password$',
        ),
        // Toutes les autres urls sont par contre sécurisées
        'secured' => array(
            'pattern' =>  '^.*$',
            'anonymous' => false,   // Définit si les connexions anonymes sont autorisées
            'remember_me' => array(
                'key'                => md5(date('Y-m-d H:i:s').'-nexecom-'.uniqid()),
                'always_remember_me' => false,
            ),
            'form' => array(
                'login_path' => '/user/login',
                'check_path' => '/user/login_check',
                'always_use_default_target_path' => true,   // Définit qu'à chaque connexion réussie on passe par le default_target_path
                'default_target_path' => '/login/redirect'  // Définit le chemin du default_target_path 
            ),
            'logout' => array(
                'logout_path' => '/user/logout',
            ),
            // Liste des utilisateurs
            'users' => $app->share(function () use ($app) {
                return $app['user.manager']; 
            }),
        ),
    ),
    // Hierarchie des roles
    'security.role_hierarchy' => array(
        'ROLE_DEV'   => array('ROLE_ADMIN', 'ROLE_USER', 'ROLE_DIRECTION'),
        'ROLE_ADMIN' => array('ROLE_USER'),
        'ROLE_DIRECTION' => array('ROLE_USER'),
    ),
    // Définit les règles d'accès 
    'security.access_rules' => array(),
));


$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());


$app->register(new Silex\Provider\RememberMeServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
// Register service providers
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

// Enregistrement du service de mail avec les options 
$app->register(
    new Silex\Provider\SwiftmailerServiceProvider(), 
    array(
        'swiftmailer.options' => $app['swiftmailer.options']
    )
);

$app['twig'] = $app->share($app->extend('twig', function(Twig_Environment $twig, $app) {
    $twig->addExtension(new Twig_Extensions_Extension_Text());
    return $twig;
}));

if (isset($app['debug']) && $app['debug']) {
    $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => __DIR__.'/../var/cache/profiler'
    ));
}

$userServiceProvider = new Masterflow\Controller\SimpleUser\UserNexecomServiceProvider();
$app->register($userServiceProvider);

$app['user.options'] = array(

    // Specify custom view templates here.
    'templates' => array(
        'layout' => 'layout.html.twig',
        'login' => 'user/login.html.twig',
        'edit' => 'user/edit.html.twig',
        'add' => 'user/add.html.twig',
        'list' => 'user/list.html.twig',
        /*'register' => '@user/register.twig',
        'register-confirmation-sent' => '@user/register-confirmation-sent.twig',
        'login' => '@user/login.twig',
        'login-confirmation-needed' => '@user/login-confirmation-needed.twig',
        'forgot-password' => '@user/forgot-password.twig',
        'reset-password' => '@user/reset-password.twig',
        'view' => 'view.twig',*/
    ),

    'userClass' => 'Masterflow\Controller\SimpleUser\UserNexecom', 

    // Configure the user mailer for sending password reset and email confirmation messages.
    'mailer' => array(
        'enabled' => true, // When false, email notifications are not sent (they're silently discarded).
        'fromEmail' => array(
            'address' => 'do-not-reply@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : gethostname()),
            'name' => 'etl-nexecom',
        ),
    ),
);

$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../var/logs/masterflow.log',
    'monolog.name' => 'Masterflow',
    'monolog.level' => $app['monolog.level']
));

// Register error handler
$app->error(function (\Exception $e, $code) use ($app) {
    switch ($code) {
        case 403:
            $message = 'Access denied.';
            break;
        case 404:
            $message = 'The requested resource could not be found.';
            break;
        default:
            $message = "Something went wrong.";
    }
    return $app['twig']->render('error.html.twig', array('message' => $message));
});

// Register JSON data decoder for JSON requests
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
    $types[] = new Masterflow\Form\Type\RoutePermissionsType();

    return $types;
}));

/************************************************************************************/
/********************************** Register services *******************************/
/************************************************************************************/
// Récupération du DAO des types utilisateurs ( ou rôle pour Silex )
$app['dao.role'] = $app->share(function ($app) {
    return new Masterflow\DAO\RoleDAO($app['db']);
});
// Récupération du DAO des routes
$app['dao.route'] = $app->share(function ($app) {
    return new Masterflow\DAO\RouteDAO($app['db']);
});
// Récupération du DAO des autorisations types utilisateur / Route
$app['dao.routePermissions'] = $app->share(function ($app) {
    $routePermissionsDAO = new Masterflow\DAO\RoutePermissionsDAO($app['db']);

    $routePermissionsDAO->setRouteDAO($app['dao.route']);
    $routePermissionsDAO->setRoleDAO($app['dao.role']);
    return $routePermissionsDAO;
});

// Register services.
$app['masterflow.masterflow.dao.dataflow'] = $app->share(function ($app) {
    return new Masterflow\Masterflow\DAO\DataflowDAO($app['db']);
});