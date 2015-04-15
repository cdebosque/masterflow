<?php

namespace Masterflow\DAO;

use Doctrine\DBAL\Connection;
use Masterflow\Domain\RoutePermissions;
use Masterflow\DAO\RouteDAO;

class RoutePermissionsDAO extends DAO
{
    /**
     * @var \Masterflow\DAO\routeDAO
     */
    private $routeDAO;

    public function setRouteDAO(RouteDAO $routeDAO) {
        $this->routeDAO = $routeDAO;
    }

    /**
     * @var \Masterflow\DAO\roleeDAO
     */
    private $roleDAO;

    public function setRoleDAO(RoleDAO $roleDAO) {
        $this->roleDAO = $roleDAO;
    }

    /**
     * Return a list of all route perissions, sorted by name.
     *
     * @return array A list of all route perissions,
     */
    public function findAll() {
        $sql = "select * from route_permissions";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $roles = array();
        foreach ($result as $row) {
            $roles[$row['id']] = $this->buildDomainObject($row);
        }
        return $roles;
    }

    /**
     * Returns a routePermission matching the supplied id.
     * @param integer $id
     * @return \Masterflow\Domain\RoutePermission|throws an exception if no matching routePermission is found
     */
    public function find($id) {
        $sql = "select * from route_permissions where id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("Aucune association route et role trouvée pour l'id " . $id);
    }

    /**
     * Renvoie les permissions associées à une route
     * @param  integer $route_id Identifiant de la route en base
     * @return \Masterflow\Domain\RoutePermission
     */
    public function getPermissionsRoute($route_id){
        // Récupère les infos de route_permissions à partir de la route_id en paramètre
        $row = $this->getDb()->fetchAll("select * from route_permissions where route_id=?", array($route_id));

        $listeRoutePermission = array();
        foreach($row as $routePermission){
            $listeRoutePermission[$routePermission['role_id']] = $routePermission;
        }

        return $listeRoutePermission;
    }

    /**
     * Renvoie toutes les routes avec les permissions si elles existent, groupées par routes.
     * @return [type] [description]
     */
    public function getRoutesPermissions($ignoreInactiveRoute = true){
        // Récupérer l'association des routes en base 
        $rolesRoutesTmp = $this->getDb()->fetchAll(
            "select routes.id, routes.path, routes.nom, GROUP_CONCAT(roles.nom) as roles 
            from routes
            left join route_permissions rp on routes.id=rp.route_id 
            left join roles on roles.id=rp.role_id
            where routes.ignore !=?
            group by routes.path
            order by routes.path asc",
            array(intval($ignoreInactiveRoute))
        );

        $rolesRoutes = array();

        foreach($rolesRoutesTmp as $rolesRoute){
            $rolesRoutes[$rolesRoute['id']] = $rolesRoute;
        }

        return $rolesRoutes;
    }

    /**
     * Renvoie la liste des types utilisateurs autorisés sur une route
     * @param  string $route Path de la route ( ex : /admin )
     * @return integer[] Liste des types utilisateurs autorisés
     */
    public function getRoleStrictForRouteName($route){
        // Récupérer l'association des routes en base 
        $routes = $this->getDb()->fetchAll(
            "select routes.path, GROUP_CONCAT(roles.nom) as roles 
            from route_permissions rp 
            inner join routes on routes.id=rp.route_id 
            inner join roles on roles.id=rp.role_id 
            where routes.path =?
            group by routes.path",
            array($route)
        );

        $roles = array();

        if(!empty($routes)){
            $routes = reset($routes);
            $roles = explode(',', $routes['roles']);
        }

        return $roles;
    }

    /**
     * Creates a RoutePermissions object based on a DB row.
     *
     * @param array $row The DB row containing Comment data.
     * @return \Masterflow\Domain\RoutePermissions
     */
    protected function buildDomainObject($row) {
        
        $routePermission = new RoutePermissions();
        $routePermission->setId($row['id']);
        $routePermission->setLastUpdate($row['last_update']);

        if (array_key_exists('route_id', $row)) {
            // Find and set the associated route
            $route_id = $row['route_id'];
            $route = $this->routeDAO->find($route_id);
            $routePermission->setRoute($route);
        }

        if (array_key_exists('role_id', $row)) {
            // Find and set the associated role
            $role_id = $row['role_id'];
            $role = $this->roleDAO->find($role_id);
            $routePermission->setRole($role);
        }
        
        return $routePermission;
    }

    /**
     * Saves a routePermission ( or type_user ) into the database.
     *
     * @param \Masterflow\Domain\RoutePermissionsDAO $role Le type d'utilisateur a sauver
     */
    public function save(RoutePermissions $routePermission) {
        $routePermissionsData = array(
            'id'            => $routePermission->getId(),
            'route_id'      => $routePermission->getRoute()->getId(),
            'role_id'       => $routePermission->getRole()->getId(),
        );

        if ($routePermission->getId()) {
            // The routePermission has already been saved : update it
            $this->getDb()->update('route_permissions', $routePermissionsData, array('id' => $routePermission->getId()));
        } 
        else {
            // The routePermission has never been saved : insert it
            $this->getDb()->insert('route_permissions', $routePermissionsData);
            // Get the id of the newly created comment and set it on the entity.
            $id = $this->getDb()->lastInsertId();
            $routePermission->setId($id);
        }

        return true;
    }

    /**
     * Suppression de l'autorisation à partir de la route et du role
     * @param  integer $roleId  [description]
     * @param  integer $routeId [description]
     * @return boolean.
     */
    public function deleteByRouteAndRole($roleId, $routeId){
        $isDeleted = false;

        if($roleId > 0 && $routeId > 0){
            $isDeleted = (bool) $this->getDb()->delete('route_permissions', array('route_id' => $routeId, 'role_id' => $roleId));
        }

        return $isDeleted;
    }
}