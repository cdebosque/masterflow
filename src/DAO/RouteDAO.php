<?php

namespace Masterflow\DAO;

use Doctrine\DBAL\Connection;
use Masterflow\Domain\Route;

class RouteDAO extends DAO
{

    /**
     * Return a list of all routes, sorted by name.
     *
     * @return array A list of all routes,
     */
    public function findAll() {
        // Récupération de toutes les routes
        $result = $this->getDb()->fetchAll("select * from routes order by path asc");
        
        $roles = array();
        foreach ($result as $row) {
            $roles[$row['id']] = $this->buildDomainObject($row);
        }
        return $roles;
    }

    /**
     * Returns a route matching the supplied id.
     * @param integer $id
     * @return \Masterflow\Domain\Route|throws an exception if no matching route is found
     */
    public function find($id) {
        // Récupération des infos en base de l'objet à partir de l'id
        $row = $this->getDb()->fetchAssoc("select * from routes where id=?", array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("Aucune route n'a été trouvée pour l'id " . $id);
    }

    /**
     * Creates a Route object based on a DB row.
     * @param array $row The DB row containing Comment data.
     * @return \Masterflow\Domain\Route
     */
    protected function buildDomainObject($row) {
        $route = new Route();
        $route->setId($row['id']);
        $route->setPath($row['path']);
        $route->setNom($row['nom']);
        $route->setLastUpdate($row['last_update']);
        
        return $route;
    }

    /**
     * Saves a route ( or type_user ) into the database.
     * @param \Masterflow\Domain\RouteDAO $role Le type d'utilisateur a sauver
     */
    public function save(Route $route) {
        $routeData = array(
            'id'    => $route->getId(),
            'path'  => $route->getPath(),
        );

        if ($route->getId()) {
            // The route has already been saved : update it
            $this->getDb()->update('routes', $routeData, array('id' => $route->getId()));
        } 
        else {
            // The route has never been saved : insert it
            $this->getDb()->insert('routes', $routeData);
            // Get the id of the newly created comment and set it on the entity.
            $id = $this->getDb()->lastInsertId();
            $route->setId($id);
        }
    }
}