<?php
class Observer extends EaiObserverProduct
{
    public function onConnectOutFinish(EaiEvent $event)
    {
        $connector= $event->getObj();

        self::setStoresByIdsV1($connector);
    }


	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    $parentId= 0;

	    if (!empty($rawData['full_path_name'])) {
    	    $rawData['full_path_name']= $rawData['full_path_name'];

    	    //$rawData['website_id']= 6;
    	    //$rawData['store_id']['en_US']= $rawData['full_path_name'];
    	    $values= array();

    	    foreach (self::$storesByLocales as $storeLocale=>$storeValues) {
    	        if (!empty($rawData['store_id'][$storeLocale]) ) {
    	            foreach ($storeValues as $store) {
    	                if( !empty($rawData['website_id']) and $store['website_id']==$rawData['website_id']) {
        	                $values[]= array('store_id'=>$store['store_id']
        	                                ,'value'=>basename($rawData['store_id'][$storeLocale]));
    	                }
    	            }

    	        }
    	    }
    	    $rawData['label']= $values;


    	    //A determiner en fonction du tir par website
    	    $storeId= 7;

    	    $rawData= array(array($parentId, $rawData, $storeId));

	    } else {
	        $event->getObj()->log('empty full_path_name');
	        $rawData= false;
	    }

	    $event->getObj()->setRawData($rawData);
	}


}