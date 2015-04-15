<?php

namespace Masterflow\Domain;

class Role 
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
     * @var string
     */
    private $nom;

    /**
     * Role description.
     *
     * @var string
     */
    private $description;

    /**
     * Role activation.
     *
     * @var boolean
     */
    private $actif;

    /**
     * Role last_update.
     *
     * @var datetime
     */
    private $last_update;

    public function __construct(){
        $this->setLastUpdate(date('Y-m-d H:i:s'));
        $this->setActif(true);
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function isActif() {
        return $this->actif;
    }

    public function setActif($actif) {
        $this->actif = (bool) $actif;
    }

     public function getLast_Update() {
        return $this->last_update;
    }

    public function setLastUpdate($last_update) {
        $this->last_update = $last_update;
    }
}