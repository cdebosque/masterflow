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

	        if ( $rawData['website_id']== 1 ) {
        	    $rawData['full_path_name']= 'Root Catalog/Catalogue dpam.com'.'/'.$rawData['full_path_name'];
	        } elseif ( $rawData['website_id']== 2 ) {
	            $rawData['full_path_name']= 'Root Catalog/Catalogue dpam.es'.'/'.$rawData['full_path_name'];
	        } elseif ( $rawData['website_id']== 3 ) {
	            $rawData['full_path_name']= 'Root Catalog/Catalogue dpam.it'.'/'.$rawData['full_path_name'];
	        } else {
	            $rawData['full_path_name']= 'Root Catalog/Catalogue dpam.com'.'/'.$rawData['full_path_name'];
	        }


    	    $values= array();
    	    foreach (self::$storesByLocales as $storeLocale=>$storeValues) {
    	        if (!empty($rawData['store_id'][$storeLocale]) ) {
    	            foreach ($storeValues as $store) {
    	                if( $store['website_id']==$rawData['website_id'])
    	                $values[]= array('store_id'=>$store['store_id']
    	                                ,'value'=>basename($rawData['store_id'][$storeLocale]));
    	            }

    	        }
    	    }
    	    $rawData['label']= $values;


    	    $storeId= null;

dump($rawData['full_path_name']);
    	    $rawData= array(array($parentId, $rawData, $storeId));
	    } else {
	        $event->getObj()->log('empty full_path_name');
	        $rawData= false;
	    }

// exit;

	    $event->getObj()->setRawData($rawData);
	}


}