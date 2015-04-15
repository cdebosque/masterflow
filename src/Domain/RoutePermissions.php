<?php

namespace Masterflow\Domain;

class RoutePermissions 
{
    /**
     * Role id.
     *
     * @var integer
     */
    private $id;

    /**
     * Role name.
     *
     * @var integer
     */
    private $role;

    /**
     * Role description.
     *
     * @var integer
     */
    private $route;

    /**
     * Role last_update.
     *
     * @var datetime
     */
    private $last_update;

    protected static $possibleRoles;

    public function __construct(){
        $this->setLastUpdate(date('Y-m-d H:i:s'));
    }

    public function __clone() {}

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole(Role $role) {
        $this->role = $role;
    }

    public function getRoute() {
        return $this->route;
    }

    public function setRoute(Route $route) {
        $this->route = $route;
    }

    public function getLast_Update() {
        return $this->last_update;
    }

    public function setLastUpdate($last_update) {
        $this->last_update = $last_update;
    }

    public function getPossibleRoles(){
        return self::$possibleRoles;
    }

    public function setPossibleRoles(array $possibleRoles){
        self::$possibleRoles = $possibleRoles;
    }


    // Fictif pour le formulaire
    public function getRoles(){
        return array();
    }

    public function setRoles(array $roles){
        
    }
}
    