<?php
class Observer extends EaiObject
{

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();
	    $rawData= array($rawData);
	    $event->getObj()->setRawData($rawData);
	}


}