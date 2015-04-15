<?php

namespace Masterflow\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Silex\ControllerProviderInterface;

class LoginRedirect implements ControllerProviderInterface {

    public function connect(Application $app) {
        $controller = $app['controllers_factory'];
        $controller->get('/', array($this, 'index'))->bind('login-redirect');
        return $controller;
    }

    /**
     * Définit où sont redirigés les utilisateurs connectés
     * @param  Application $app 
     * @return void.
     */
    public function index(Application $app) {

        // @continue
        if ($app['security']->isGranted('ROLE_ADMIN')) {
            return $app->redirect($app['url_generator']->generate('admin'));
        }

        return $app->redirect($app['url_generator']->generate('home-user'));
    }

}