<?php

class Observer Extends EaiObserverCrosslog {

  static $connectorIn = null;
  static $incrementId = '';

  public function onConnectInFinish(EaiEvent $event){
    self::$connectorIn = $event->getObj();
  }

  public function beforeSend(EaiEvent $event)
	{
		$rawData= $event->getObj()->getRawData();
		$result = $rawData['increment_id'];
		
		$event->getObj()->setRawData(array($result
		));
	}

}
