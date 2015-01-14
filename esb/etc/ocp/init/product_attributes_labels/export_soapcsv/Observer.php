<?php
class Observer extends EaiObserverProduct
{
//     static $storesByKeys=array();

//     public function onConnectInFinish(EaiEvent $event)
//     {
//         $connector= $event->getObj();

//         $result = $connector->getClient()->call($connector->getSession(), 'store.list');
//         foreach($result as $resKey=>$resVal )
//             self::$storesByKeys[$resVal['code']]= $resVal['store_id'];


//     }


    public function onConnectInFinish(EaiEvent $event)
    {
        $connector= $event->getObj();

        self::setStoresByIdsV1($connector);
    }

    public function beforeMap(EaiEvent $event)
    {
	    $element= $event->getObj()->getElement();


	    if(!empty($element['store_labels'])){
    	    dump($element);
	    }

// 	    dump(self::$storesByKeys);
	    $custElement= $element;

	    foreach(self::$storesByIds as $storeId=>$store) {
	        if(isset($element['store_labels'][$storeId])) {
	            $custElement[$store['locale']]= $element['store_labels'][$storeId];
	        }
	    }

// 	    $custElement['store_fr']= $element['store_labels'][self::$storesByKeys['dpam_fr']]['label'];
// 	    $custElement['store_es']= $element['store_labels'][self::$storesByKeys['dpam_es']]['label'];
// 	    $custElement['store_it']= $element['store_labels'][self::$storesByKeys['dpam_it']]['label'];
// 	    $custElement['store_en']= $element['store_labels'][self::$storesByKeys['dpam_en']]['label'];

dump($custElement);
	    $event->getObj()->setElement($custElement);
// 	    dump($custElement);
// 	    exit;
    }

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	}


}