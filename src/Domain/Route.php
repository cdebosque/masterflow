<?php

namespace Masterflow\Domain;

class Route 
{
    /**
     * Route id.
     *
     * @var integer
     */
    private $id;

    /**
     * Route path.
     *
     * @var string
     */
    private $path;

    /**
     * Route nom.
     *
     * @var string
     */
    private $nom;

    /**
     * Ignore Route.
     *
     * @var bool
     */
    private $ignore;

    /**
     * Route last_update.
     *
     * @var datetime
     */
    private $last_update;

    public function __construct(){
        $this->setLastUpdate(date('Y-m-d H:i:s'));
        $this->ignore = false;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function isIgnore() {
        return $this->ignore;
    }

    public function setIgnore($isIgnore) {
        $this->ignore = (bool) $isIgnore;
    }

    public function getLast_Update() {
        return $this->last_update;
    }

    public function setLastUpdate($last_update) {
        $this->last_update = $last_update;
    }
}
    