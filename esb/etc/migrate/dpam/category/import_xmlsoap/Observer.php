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

	        if ( strpos($rawData['full_path_name'], 'Catalogue dpam.com')!=false  ) {
        	    $rawData['website_id']= 1;
	        } elseif ( strpos($rawData['full_path_name'], 'Catalogue dpam.es')!=false ) {
	            $rawData['website_id']= 2;
// 	            $rawData= false;
	        } elseif ( strpos($rawData['full_path_name'], 'Catalogue dpam.it')!=false ) {
	            $rawData['website_id']= 3;
// 	            $rawData= false;
	        }


	        //$rawData['richmenu_content']= htmlentities();


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

    	    $rawData= array(array($parentId, $rawData, $storeId));
	    } else {
	        $event->getObj()->log('empty full_path_name');
	        $rawData= false;
	    }

// dump($rawData);
// exit;

	    $event->getObj()->setRawData($rawData);
	}


}