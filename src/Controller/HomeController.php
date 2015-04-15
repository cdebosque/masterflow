<?php

namespace Masterflow\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Masterflow\Domain\Comment;
use Masterflow\Form\Type\CommentType;

class HomeController {

    /**
     * Home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        return $app['twig']->render('index.html.twig', array());
    }
}
