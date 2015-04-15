<?php

namespace Masterflow\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AdminController {

    /**
     * Admin home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        return $app['twig']->render('admin.html.twig', array());
    }
}
