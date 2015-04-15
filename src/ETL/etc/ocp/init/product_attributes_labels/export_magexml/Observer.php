<?php
class Observer extends EaiObject
{
    static $storesByKeys=array();

    public function onConnectInFinish(EaiEvent $event)
    {
        foreach (Mage::app()->getStores() as $store) {
            self::$storesByKeys[$store->getCode()]= $store->getId();
        }
    }

    public function beforeMap(EaiEvent $event)
    {
	    $element= $event->getObj()->getElement();
	    $custElement= $element;
	    $custElement['store_fr']= $element['store_labels'][self::$storesByKeys['dpam_fr']]['label'];
	    $custElement['store_es']= $element['store_labels'][self::$storesByKeys['dpam_es']]['label'];
	    $custElement['store_it']= $element['store_labels'][self::$storesByKeys['dpam_it']]['label'];
	    $custElement['store_en']= $element['store_labels'][self::$storesByKeys['dpam_en']]['label'];


	    $event->getObj()->setElement($custElement);
    }
}