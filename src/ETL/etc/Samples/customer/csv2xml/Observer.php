<?php
class Observer extends EaiObject
{

	public static function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    //$custEaiData= array($rawData['attribute_code'],$rawData);
	    $custEaiData= array(array($rawData));
	    $event->getObj()->setRawData($custEaiData);
	}


}