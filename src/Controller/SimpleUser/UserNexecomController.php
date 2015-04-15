<?php

namespace Masterflow\Controller\SimpleUser;

use SimpleUser\UserController;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use InvalidArgumentException;
use JasonGrimes\Paginator;

/**
 * Controller with actions for handling form-based authentication and user management.
 *
 * @package SimpleUser
 */
class UserNexecomController extends UserController
{

    /**
     * Register action.
     *
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function registerAction(Application $app, Request $request)
    {
        if ($request->isMethod('POST')) {
            try {
                $user = $this->createUserFromRequest($request);
                if ($error = $this->userManager->validatePasswordStrength($user, $request->request->get('password'))) {
                    throw new InvalidArgumentException($error);
                }
                if ($this->isEmailConfirmationRequired) {
                    $user->setEnabled(false);
                    $user->setConfirmationToken($app['user.tokenGenerator']->generateToken());
                }
                $this->userManager->insert($user);

                if ($this->isEmailConfirmationRequired) {
                    // Send email confirmation.
                    $app['user.mailer']->sendConfirmationMessage($user);

                    // Render the "go check your email" page.
                    return $app['twig']->render($this->getTemplate('register-confirmation-sent'), array(
                        'layout_template' => $this->getTemplate('layout'),
                        'email' => $user->getEmail(),
                    ));
                } else {
                    // Log the user in to the new account.
                    $this->userManager->loginAsUser($user);

                    $app['session']->getFlashBag()->set('alert', 'Account created.');

                    // Redirect to user's new profile page.
                    return $app->redirect($app['url_generator']->generate('user.view', array('id' => $user->getId())));
                }

            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            }
        }

        return $app['twig']->render($this->getTemplate('register'), array(
            'layout_template' => $this->getTemplate('layout'),
            'error' => isset($error) ? $error : null,
            'name' => $request->request->get('name'),
            'email' => $request->request->get('email'),
            'username' => $request->request->get('username'),
            'isUsernameRequired' => $this->isUsernameRequired,
        ));
    }

    /**
     * Action to handle email confirmation links.
     *
     * @param Application $app
     * @param Request $request
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function confirmEmailAction(Application $app, Request $request, $token)
    {
        $user = $this->userManager->findOneBy(array('confirmationToken' => $token));
        if (!$user) {
            $app['session']->getFlashBag()->set('alert', 'Sorry, your email confirmation link has expired.');

            return $app->redirect($app['url_generator']->generate('user.login'));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $this->userManager->update($user);

        $this->userManager->loginAsUser($user);

        $app['session']->getFlashBag()->set('alert', 'Thank you! Your account has been activated.');

        return $app->redirect($app['url_generator']->generate('user.view', array('id' => $user->getId())));
    }

    /**
     * @param Request $request
     * @return User
     * @throws InvalidArgumentException
     */
    protected function createUserFromRequest(Request $request)
    {
        if ($request->request->get('password') != $request->request->get('confirm_password')) {
            throw new InvalidArgumentException('Passwords don\'t match.');
        }

        $user = $this->userManager->createUser(
            $request->request->get('email'),
            $request->request->get('password'),
            $request->request->get('name') ?: null);

        if ($username = $request->request->get('username')) {
            $user->setUsername($username);
        }

        $errors = $this->userManager->validate($user);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode("\n", $errors));
        }

        return $user;
    }


    /**
     * Edit user action.
     *
     * @param Application $app
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if no user is found with that ID.
     */
    public function editAction(Application $app, Request $request, $id)
    {
        $errors = array();

        $user = $this->userManager->getUser($id);
        if (!$user) {
            throw new NotFoundHttpException('No user was found with that ID.');
        }

        $customFields = $this->editCustomFields ?: array();

        if ($request->isMethod('POST')) {
            $user->setName($request->request->get('name'));
            $user->setEmail($request->request->get('email'));
            if ($request->request->has('username')) {
                $user->setUsername($request->request->get('username'));
            }
            if ($request->request->get('password')) {
                if ($request->request->get('password') != $request->request->get('confirm_password')) {
                    $errors['password'] = 'Passwords don\'t match.';
                } else if ($error = $this->userManager->validatePasswordStrength($user, $request->request->get('password'))) {
                    $errors['password'] = $error;
                } else {
                    $this->userManager->setUserPassword($user, $request->request->get('password'));
                }
            }
            if ($app['security']->isGranted('ROLE_ADMIN') && $request->request->has('roles')) {
                $user->setRoles($request->request->get('roles'));
            }

            foreach (array_keys($customFields) as $customField) {
                if ($request->request->has($customField)) {
                    $user->setCustomField($customField, $request->request->get($customField));
                }
            }

            $errors += $this->userManager->validate($user);

            if (empty($errors)) {
                $this->userManager->update($user);
                $msg = 'Saved account information.' . ($request->request->get('password') ? ' Changed password.' : '');
                $app['session']->getFlashBag()->set('alert', $msg);
            }
        }

        if(!empty($app['security.role_hierarchy'])){
            $roles = array();
            foreach($app['security.role_hierarchy'] as $role => $sousRole){
                $roles[] = $role;
                $roles += $sousRole;
            }

            $roles = array_unique($roles);
            sort($roles);
        }
        else{
            $roles = array('ROLE_USER', 'ROLE_ADMIN');
        }
        
        return $app['twig']->render($this->getTemplate('edit'), array(
            'layout_template' => $this->getTemplate('layout'),
            'error' => implode("\n", $errors),
            'user' => $user,
            'available_roles' => $roles,
            'image_url' => $this->getGravatarUrl($user->getEmail()),
            'customFields' => $customFields,
            'isUsernameRequired' => $this->isUsernameRequired,
        ));
    }


    public function listAction(Application $app, Request $request)
    {
        $order_by = $request->get('order_by') ?: 'name';
        $order_dir = $request->get('order_dir') == 'DESC' ? 'DESC' : 'ASC';
        $limit = (int)($request->get('limit') ?: 50);
        $page = (int)($request->get('page') ?: 1);
        $offset = ($page - 1) * $limit;

        $criteria = array();
        if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $criteria['isEnabled'] = true;
        }

        $users = $this->userManager->findBy($criteria, array(
            'limit' => array($offset, $limit),
            'order_by' => array($order_by, $order_dir),
        ));
        $numResults = $this->userManager->findCount($criteria);

        $paginator = new Paginator($numResults, $limit, $page,
            $app['url_generator']->generate('user.list') . '?page=(:num)&limit=' . $limit . '&order_by=' . $order_by . '&order_dir=' . $order_dir
        );

        foreach ($users as $user) {
            $user->imageUrl = $this->getGravatarUrl($user->getEmail(), 40);
        }

        return $app['twig']->render($this->getTemplate('list'), array(
            'layout_template' => $this->getTemplate('layout'),
            'users' => $users,
            'paginator' => $paginator,

            // The following variables are no longer used in the default template,
            // but are retained for backward compatibility.
            'numResults' => $paginator->getTotalItems(),
            'nextUrl' => $paginator->getNextUrl(),
            'prevUrl' => $paginator->getPrevUrl(),
            'firstResult' => $paginator->getCurrentPageFirstItem(),
            'lastResult' => $paginator->getCurrentPageLastItem(),
        ));
    }

    /**
     * Ajout d'un nouvel utilisateur
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function addAction(Application $app, Request $request)
    {
        if ($request->isMethod('POST')) {
            try {
                $user = $this->createUserFromRequest($request);
                if ($error = $this->userManager->validatePasswordStrength($user, $request->request->get('password'))) {
                    throw new InvalidArgumentException($error);
                }
                if ($this->isEmailConfirmationRequired) {
                    $user->setEnabled(false);
                    $user->setConfirmationToken($app['user.tokenGenerator']->generateToken());
                }
                $this->userManager->insert($user);

                if ($this->isEmailConfirmationRequired) {
                    // Send email confirmation.
                    $app['user.mailer']->sendConfirmationMessage($user);

                    // Render the "go check your email" page.
                    return $app['twig']->render($this->getTemplate('register-confirmation-sent'), array(
                        'layout_template' => $this->getTemplate('layout'),
                        'email' => $user->getEmail(),
                    ));
                } else {
                    $app['session']->getFlashBag()->set('alert', 'Le compte a été créé avec succès.');

                    // Redirect to user's new profile page.
                    return $app->redirect($app['url_generator']->generate('user.list'));
                }

            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            }
        }

        return $app['twig']->render($this->getTemplate('add'), array(
            'layout_template' => $this->getTemplate('layout'),
            'error' => isset($error) ? $error : null,
            'name' => $request->request->get('name'),
            'email' => $request->request->get('email'),
            'username' => $request->request->get('username'),
            'isUsernameRequired' => $this->isUsernameRequired,
        ));
    }

    /**
     * @param boolean $passwordResetEnabled
     */
    public function setPasswordResetEnabled($passwordResetEnabled)
    {
        $this->isPasswordResetEnabled = (bool) $passwordResetEnabled;
    }

    /**
     * @return boolean
     */
    public function isPasswordResetEnabled()
    {
        return $this->isPasswordResetEnabled;
    }

    /**
     * @param bool $isUsernameRequired
     */
    public function setUsernameRequired($isUsernameRequired)
    {
        $this->isUsernameRequired = (bool) $isUsernameRequired;
    }

    public function setEmailConfirmationRequired($isRequired)
    {
        $this->isEmailConfirmationRequired = (bool) $isRequired;
    }

    // ---------------------------------------------------------------------------
    //
    // Deprecated methods.
    //
    // Retained for backwards compatibility.
    //
    // ---------------------------------------------------------------------------

    /**
     * @param string $layoutTemplate
     * @deprecated Use setTemplate() or setTemplates() instead.
     */
    public function setLayoutTemplate($layoutTemplate)
    {
        $this->setTemplate('layout', $layoutTemplate);
    }

    /**
     * @deprecated Use setTemplate() or setTemplates() instead.
     * @param string $editTemplate
     */
    public function setEditTemplate($editTemplate)
    {
        $this->setTemplate('edit', $editTemplate);
    }

    /**
     * @deprecated Use setTemplate() or setTemplates() instead.
     * @param string $listTemplate
     */
    public function setListTemplate($listTemplate)
    {
        $this->setTemplate('list', $listTemplate);
    }

    /**
     * @deprecated Use setTemplate() or setTemplates() instead.
     * @param string $loginTemplate
     */
    public function setLoginTemplate($loginTemplate)
    {
        $this->setTemplate('login', $loginTemplate);
    }

    /**
     * @deprecated Use setTemplate() or setTemplates() instead.
     * @param string $registerTemplate
     */
    public function setRegisterTemplate($registerTemplate)
    {
        $this->setTemplate('register', $registerTemplate);
    }

    /**
     * @deprecated Use setTemplate() or setTemplates() instead.
     * @param string $viewTemplate
     */
    public function setViewTemplate($viewTemplate)
    {
        $this->setTemplate('view', $viewTemplate);
    }
}
