<?php
/**
 * Regroupement des traitements génériques sur une interface via un Observer
 * Ne PAS inclure systématiquement cette classe, si ce code est déja gérée dans un obsserver local par exemple.
 *
 * 2 Possiblités d'utilisation :
 * - surcharge des méthodes : créer un observer local qui extends EaiObserverStandard (il sera autoloadé), les méthodes sur les events seront surchargées, par cumulées
 * - cumul des méthodes : inclure directement cet observer dans interface.xml : <observer>EaiObserverStandard</observer> (il sera trouvé sans préciser le chemin). Les observers seront exécutés dans l'ordre de déclaration
 *
 * @author Thomas
 */
class EaiObserverStandard extends EaiObject
{
    /**
     * Gère l'imbriquation en sous-tableau pour Soap et Mage
     * @param EaiEvent $event
     */
    public function beforeSend(EaiEvent $event)
    {
        /* @var EaiConnector $connector */
        $connector = $event->getObj(); //pointeur sur l'objet EaiConnector (par référence)

        static::prepareSending($connector);

    }

    static protected function prepareSending($connector)
    {
        $rawData = $connector->getRawData(); //copie du tableau (par valeur)

        switch($connector->getClass()) {
            case "EaiConnectorSoap":
                $connector->setRawData(array(array($rawData)));
                break;
            case "EaiConnectorMage":
                $connector->setRawData(array($rawData));
                break;
        }
    }
}