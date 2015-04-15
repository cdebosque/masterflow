<?php

namespace Masterflow\Controller\SimpleUser;

use SimpleUser\UserServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Silex\ServiceControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Surcharge de la classe SimpleUser\UserServiceProvider pour pouvoir changer les paramètres et le fonctionnement
 */
class UserNexecomServiceProvider extends UserServiceProvider
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {

        parent::register($app);

        // Surcharge avec le User controller service Nexecom.
        $app['user.controller'] = $app->share(function ($app) {
            $app['user.options.init']();

            $controller = new UserNexecomController($app['user.manager']);
            $controller->setUsernameRequired($app['user.options']['isUsernameRequired']);
            $controller->setEmailConfirmationRequired($app['user.options']['emailConfirmation']['required']);
            $controller->setTemplates($app['user.options']['templates']);
            $controller->setEditCustomFields($app['user.options']['editCustomFields']);

            return $controller;
        });
        
    }

    /**
     * Returns routes to connect to the given application.
     * Surcharge des routes afin de pouvoir sécuriser les accès de certaines fonctionnalités
     * @param Application $app An Application instance
     * @return ControllerCollection A ControllerCollection instance
     * @throws \LogicException if ServiceController service provider is not registered.
     */
    public function connect(Application $app)
    {
        if (!$app['resolver'] instanceof ServiceControllerResolver) {
            // using RuntimeException crashes PHP?!
            throw new \LogicException('You must enable the ServiceController service provider to be able to use these routes.');
        }

        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'user.controller:viewSelfAction')
            ->bind('user')
            ->before(function(Request $request) use ($app) {
                // Require login. This should never actually cause access to be denied,
                // but it causes a login form to be rendered if the viewer is not logged in.
                if (!$app['user']) {
                    throw new AccessDeniedException();
                }
            });

        $controllers->get('/{id}', 'user.controller:viewAction')
            ->bind('user.view')->secure(array())
            ->assert('id', '\d+');

        $controllers->method('GET|POST')->match('/{id}/edit', 'user.controller:editAction')
            ->bind('user.edit')->secure(array())
            ->before(function(Request $request) use ($app) {
                if (!$app['security']->isGranted('EDIT_USER_ID', $request->get('id'))) {
                    throw new AccessDeniedException();
                }
            });

        $controllers->get('/list', 'user.controller:listAction')
            ->bind('user.list')->secure(array());

        $controllers->method('GET|POST')->match('/register', 'user.controller:registerAction')
            ->bind('user.register');

        $controllers->get('/confirm-email/{token}', 'user.controller:confirmEmailAction')
            ->bind('user.confirm-email');

        $controllers->post('/resend-confirmation', 'user.controller:resendConfirmationAction')
            ->bind('user.resend-confirmation');

        $controllers->get('/login', 'user.controller:loginAction')
            ->bind('user.login');

        $controllers->method('GET|POST')->match('/forgot-password', 'user.controller:forgotPasswordAction')
            ->bind('user.forgot-password');
            
        $controllers->match('/add', 'user.controller:addAction')
            ->bind('user.add');

        $controllers->get('/reset-password/{token}', 'user.controller:resetPasswordAction')
            ->bind('user.reset-password');

        // login_check and logout are dummy routes so we can use the names.
        // The security provider should intercept these, so no controller is needed.
        $controllers->method('GET|POST')->match('/login_check', function() {})
            ->bind('user.login_check');
        $controllers->get('/logout', function() {})
            ->bind('user.logout');

        return $controllers;
    }
}
