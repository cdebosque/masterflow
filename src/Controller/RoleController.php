<?php

namespace Masterflow\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

// Chargement des namespaces des domaines
use Masterflow\Domain\Role;
use Masterflow\Domain\Route;
use Masterflow\Domain\RoutePermissions;

// Chargement des namespace des classes de formulaires
use Masterflow\Form\Type\RoleType;
use Masterflow\Form\Type\RoutePermissionsType;

class RoleController {

    /**
     * Listings des type d'utilisateur ( ROLES )
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        // Récupérer la liste des roles
        $roles = $app['dao.role']->findAll();

        // Renvoie de la réponse
        return $app['twig']->render('roles-list.html.twig', array('roles' => $roles));
    }

    /**
     * Création d'un nouveau rôle
     * @param  Application $app [description]
     * @return [type]           [description]
     */
    public function createRoleAction(Application $app, Request $request, $id=0){

        if($id >0){
            $role = $app['dao.role']->find($id);
        }
        else{
            $role = new Role();
        }

        // Récupération de l'objet form des types utilisateurs
        $roleForm = $app['form.factory']->create(new RoleType(), $role);

        $roleForm->handleRequest($request);

        if ($roleForm->isSubmitted() && $roleForm->isValid()) {
            try{
                $app['dao.role']->save($role);

                $app['session']->getFlashBag()->add('success', 'Le type d\'utilisateur '.$role->getNom().' a été créé.');

                return $app->redirect($app['url_generator']->generate('list-roles'));
            }
            catch(\Exception $e){
                $app['session']->getFlashBag()->add('error', $e->getMessage());
            }
        }

        // Récupération de la vue du formulaire
        $roleFormView = $roleForm->createView();

        // Renvoie de la réponse
        return $app['twig']->render('create-role.html.twig', array('roleForm' => $roleFormView));
    }

    /**
     * Listes des types utilisateurs autorisés sur les routes
     * @param  Application $app [description]
     * @return [type]           [description]
     */
    public function listeRolesRouteAction(Application $app){
        // Récupère les routes actives 
        $rolesRoutes = $app['dao.routePermissions']->getRoutesPermissions();

        // Renvoie de la réponse dans le template
        return $app['twig']->render('roles-routes.html.twig', array('rolesroutes' => $rolesRoutes));
    }

    /**
     * Permet d'autoriser des types d'utilisateurs sur des routes
     * @param  Application $app     [description]
     * @param  Request     $request [description]
     * @param  integer     $id      [description]
     * @return Response.
     */
    public function editRolesRouteAction(Application $app, Request $request, $id=0){

        // Récupération des permissions sur la route si elle existe
        $routePermissionsDetails = $app['dao.routePermissions']->getPermissionsRoute($id);

        // Si il n'y a pas encore de permissions sur cette route
        //if(!$routePermissions instanceof RoutePermissions) {
            // Instanciatopn de l'objet
            $routePermissions = new RoutePermissions();

            // Recherche de la route concernée
            $route = $app['dao.route']->find($id);

            // Génération d'une exception si la route n'existe pas
            if(!$route instanceof Route){
                throw new Exception("La route n'a pas été trouvée");
            }

            // On définit la route dans les permissions
            $routePermissions->setRoute($route);
        //}

        // Roles possibles
        $possibleRoles = $app['dao.role']->findAll();

        $routePermissions->setPossibleRoles($possibleRoles);

        // Récupération de l'objet form des types utilisateurs
        $routePermissionsForm = $app['form.factory']->create(new RoutePermissionsType(), $routePermissions);

        if ($request->isMethod('POST')) {

            // Récupération des éventuels données en requêtes
            $routePermissionsForm->handleRequest($request);
            

            // Si le formulaire a été soumis alors 
            if ($routePermissionsForm->isSubmitted() && $routePermissionsForm->isValid()) {
                try{
                    $postDatas = $request->request->get($routePermissionsForm->getName());

                    if(empty($postDatas['roles'])){
                        throw new Exception("Aucun type d'utilisateur n'a été sélectionné.");
                    }

                    foreach($postDatas['roles'] as $roleId){

                        $routePermissionsTmp = clone $routePermissions;

                        // Il existe deja donc on ne l'enregistre pas
                        if(isset($routePermissionsDetails[$roleId])){
                            continue;
                        }

                        // Recherche de l'objet role
                        $role = $app['dao.role']->find($roleId);

                        // Si le role n'existe pas on ne fait rien
                        if(!$role instanceof Role){
                            continue;
                        }

                        // Définit le rôle
                        $routePermissionsTmp->setRole($role);

                        // Sauvegarde de l'objet edité/créé
                        $app['dao.routePermissions']->save($routePermissionsTmp);
                    }

                    // Si il y a des types d'utilisateur qui n'ont plus le droit de consulter la page
                    $deletedRoles = array_diff(array_keys($routePermissionsDetails), $postDatas['roles']);

                    foreach($deletedRoles as $deletedRoleId){
                        // Suppression de l'objet 
                        $app['dao.routePermissions']->deleteByRouteAndRole($deletedRoleId, $id);
                    }

                    // Ajout du message de succès
                    $app['session']->getFlashBag()->add('success', 'Le type des droits pour la route a été créé/mis à jour.');

                    // Redirection vers la liste des routes
                    return $app->redirect($app['url_generator']->generate('roles-route'));
                }
                catch(\Exception $e){
                    // Ajout d'un message d'erreur
                    $app['session']->getFlashBag()->add('error', $e->getMessage());
                }
            }
        }

        // Récupération de la vue du formulaire
        $routePermissionsFormView = $routePermissionsForm->createView();

        // Renvoie de la réponse
        return $app['twig']->render(
            'roles-routes-edit.html.twig', 
            array(
                'routePermissionsForm' => $routePermissionsFormView, 
                'routePath' => $routePermissions->getRoute()->getPath(), 
                'routeName' => $routePermissions->getRoute()->getNom()
            )
        );
    }

    /**
     * Permet d'enreistrer le role
     * @param  Application $app     [description]
     * @param  Request     $request [description]
     * @param  integer     $id      [description]
     * @return [type]               [description]
     */
    public function saveRole(Application $app, Request $request, $id = 0){

        $role       = $app['dao.role']->find($id);
        $user       = $app['security']->getToken()->getUser();

        $roleFormView = null;

        if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
            // A user is fully authenticated : he can add comments
            $role = new Role();
           
            $roleForm = $app['form.factory']->create(new RoleType(), $role);
            $roleForm->handleRequest($request);
            if ($roleForm->isSubmitted() && $roleForm->isValid()) {
                $app['dao.role']->save($roleForm);
                $app['session']->getFlashBag()->add('success', 'Le role a été modifié/ajouté avec succès.');
            }
            $roleFormView = $roleForm->createView();
        }

        return $app['twig']->render(
            'article.html.twig', 
            array(
                'roleForm' => $roleFormView
            )
        );
    }
}
