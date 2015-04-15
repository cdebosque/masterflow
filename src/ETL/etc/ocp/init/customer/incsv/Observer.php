<?php
class Observer extends EaiObserverCore
{


  public function onConnectOutFinish(EaiEvent $event) {
    $connector = $event->getObj();

    self::setStoresByIdsMage($connector);
  }

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();
      $store = self::$storesByCodes[$rawData['_website']];
      $rawData['website_id'] = $store['website_id'];
      $rawData['store_id'] = $store['store_id'];
	    $rawData= array($rawData);
	    $event->getObj()->setRawData($rawData);
	}

}