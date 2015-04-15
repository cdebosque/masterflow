<?php
class Observer extends EaiObserverProduct
{

    //public function onFinish_EaiConnectorSoap_connect(EaiEvent $event)
    public function onConnectOutFinish(EaiEvent $event)
    {
        $connector= $event->getObj();

        self::setStoresByIdsV1($connector);
    }

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    $values= array();
	    $values[]= array('store_id'=>0, 'value'=>$rawData['store_id']['default']);
	    foreach (self::$storesByLocales as $storeLocale=>$storeValues) {
	        if (isset($rawData['store_id'][$storeLocale])) {
	            foreach ($storeValues as $store) {

    	            $values[]= array('store_id'=>$store['store_id']
    	                            ,'value'=>ucwords(strtolower(preg_replace('/([^\d]*)(\d+%)(\w)/', '$1 $2 $3', $rawData['store_id'][$storeLocale]))));
	            }
	        }
	    }

        $custEaiData= array(array($rawData['attribute_code'], array('label' => $values)));

	    $event->getObj()->setRawData($custEaiData);
	}
}