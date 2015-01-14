<?php
class Observer extends EaiObserverCore
{

    public function onConnectOutFinish(EaiEvent $event)
    {
        $connector= $event->getObj();
        self::setStoresByIdsV1($connector);
    }

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    $custEaiData= array(array($rawData['attribute_code'],
	                        array('frontend_label'=>self::getValuesByStoreid($rawData['store_id'], 'store_id', 'label')))
	    );

	    $event->getObj()->setRawData($custEaiData);
	}

}