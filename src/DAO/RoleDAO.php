<?php

namespace Masterflow\DAO;

use Doctrine\DBAL\Connection;
use Masterflow\Domain\Role;

class RoleDAO extends DAO
{

    /**
     * Return a list of all roles, sorted by name.
     * @return array A list of all roles.
     */
    public function findAll() {

        // Récupération des différents type d'utilisateur
        $result = $this->getDb()->fetchAll("select * from roles order by nom asc");
        
        $roles = array();
        foreach ($result as $row) {
            $roles[$row['id']] = $this->buildDomainObject($row);
        }
        return $roles;
    }

    /**
     * Returns a role matching the supplied id.
     * @param integer $id
     * @return \Masterflow\Domain\Role|throws an exception if no matching article is found
     */
    public function find($id) {
        // Récupération du rôle à partir de son id
        $row = $this->getDb()->fetchAssoc("select * from roles where id=?", array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("Aucun role trouvé pour l'id " . $id);
    }

    /**
     * Creates a Role object based on a DB row.
     * @param array $row The DB row containing Comment data.
     * @return \Masterflow\Domain\Role
     */
    protected function buildDomainObject($row) {
        $role = new Role();
        $role->setId($row['id']);
        $role->setNom($row['nom']);
        $role->setDescription($row['description']);
        $role->setActif($row['actif']);
        $role->setLastUpdate($row['last_update']);
       
        return $role;
    }

    /**
     * Saves a role ( or type_user ) into the database.
     *
     * @param \Masterflow\Domain\Role $role Le type d'utilisateur a sauver
     */
    public function save(Role $role) {
        $roleData = array(
            'id'            => $role->getId(),
            'nom'           => $role->getNom(),
            'description'   => $role->getDescription() !== null? $role->getDescription() : '',
            'actif'         => $role->isActif(),
        );

        // Vérifie que le prefixe utilisé est correct
        if(!preg_match('`^ROLE_`', $role->getNom())){
            throw new \Exception("Le type utilisateur doit obligatoirement commencer par ROLE_");
        }

        // Si le rôle existe déjà 
        if ($role->getId()) {
            // The role has already been saved : update it
            $this->getDb()->update('roles', $roleData, array('id' => $role->getId()));
        } 
        else {
            // The role has never been saved : insert it
            $this->getDb()->insert('roles', $roleData);
            // Get the id of the newly created comment and set it on the entity.
            $id = $this->getDb()->lastInsertId();
            $role->setId($id);
        }

        return true;
    }
}