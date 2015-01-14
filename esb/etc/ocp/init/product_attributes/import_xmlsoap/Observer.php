<?php
class Observer extends EaiObject
{

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

// 	    dump($rawData);

	    $rawData['store_labels']= array();
	    $rawData= array(array($rawData));
	    $event->getObj()->setRawData($rawData);
	}


}