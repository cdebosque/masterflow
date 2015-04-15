<?php
class Observer extends EaiObject
{

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    $custEaiData= array($rawData['attribute_code'],$rawData);
	    $event->getObj()->setRawData($custEaiData);
	}


}