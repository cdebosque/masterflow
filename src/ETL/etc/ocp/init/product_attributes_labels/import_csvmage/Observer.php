<?php
class Observer extends EaiObject
{
    static $storesByKeys=array();

    public function onConnectOutFinish(EaiEvent $event)
    {

        foreach (Mage::app()->getStores() as $store) {
            self::$storesByKeys[$store->getCode()]= $store->getId();
        }
    }


	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    $custEaiData= array($rawData['attribute_code'],array('front_end_label'=>array(array('store_id'=>0
                                                                ,'label'=>$rawData['admin']),
                                                           array('store_id'=>self::$storesByKeys['dpam_fr']
                                                                ,'label'=>$rawData['store_fr']),
                                                           array('store_id'=>self::$storesByKeys['dpam_es']
                                                                ,'label'=>$rawData['store_es']),
                                                           array('store_id'=>self::$storesByKeys['dpam_it']
                                                                ,'label'=>$rawData['store_it']),
                                                           array('store_id'=>self::$storesByKeys['dpam_en']
                                                                ,'label'=>$rawData['store_en'])
                                   )));
	    $event->getObj()->setRawData($custEaiData);
	}


}