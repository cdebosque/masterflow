<?php
class Observer extends EaiObject
{
    static $storesByKeys=array();

    public function onConnectInFinish(EaiEvent $event)
    {
        $connector= $event->getObj();

        $result = $connector->getClient()->call($connector->getSession(), 'store.list');
        foreach($result as $resKey=>$resVal )
            self::$storesByKeys[$resVal['code']]= $resVal['store_id'];
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