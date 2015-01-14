<?php
class Observer extends EaiObserverProduct
{

    


	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();
	    $custEaiData= array($rawData);
	    $event->getObj()->setRawData($custEaiData);
	}


}