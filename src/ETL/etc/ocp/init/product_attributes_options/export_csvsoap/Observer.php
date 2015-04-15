<?php
class Observer extends EaiObserverProduct
{
    static $storesByKeys=array();

    public function onConnectInFinish(EaiEvent $event)
    {
        $connector= $event->getObj();
        $result = $connector->getClient()->call($connector->getSession(), 'store.list');
        foreach($result as $resKey=>$resVal)
            self::$storesByKeys[$resVal['code']]= $resVal['store_id'];

        self::setStoresByIdsV1($connector);
    }

    public function onFetchRawDataInFinish(EaiEvent $event)
    {
        $rawData= $event->getObj()->getRawData();


        dump($rawData);
    }

	public function beforeSend(EaiEvent $event)
	{



	    $values= array();
	    $values[]= array('store_id'=>0, 'value'=>$rawData['store_id']['default']);
	    foreach (self::$storesByLocales as $storeLocale=>$storeValues) {
	        if (isset($rawData['store_id'][$storeLocale])) {
	            foreach ($storeValues as $store) {
    	            $values[]= array('store_id'=>$store['store_id'], 'value'=>$rawData['store_id'][$storeLocale]);
	            }

	        }
	    }

        $custEaiData= array(array($rawData['attribute_code'], array('label'=>$values)));

	    $event->getObj()->setRawData($custEaiData);
	}
}