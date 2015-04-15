<?php 

namespace Masterflow\Config;

use Silex\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Masterflow\Controller\RoleController;

class NexecomRoute extends Route
{
    use Route\SecurityTrait;

    // Permet de n'autoriser que les roles associés aux routes en base
    public function secure($roles)
    {
        $this->before(function ($request, $app) use ($roles) {
            
            // Récupération de l'objet route courant demandé
            $route = $app['routes']->get($request->get('_route'));

            // Récupération du path de la route demandée
            $currentPathRoute = $route->getPath();

            // Récupération des roles autorisés pour cette route
            $roles = $app['dao.routePermissions']->getRoleStrictForRouteName($currentPathRoute);

            // Récupération du rôle DEV partout par défaut
            $roles = empty($roles)? array('ROLE_DEV') : $roles;

            if (!$app['security']->isGranted($roles)) {
                throw new AccessDeniedException();
            }
        });
    }
}