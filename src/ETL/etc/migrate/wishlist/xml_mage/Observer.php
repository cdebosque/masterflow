<?php
class Observer extends EaiObserverProduct
{

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

      

      
      
	    $event->getObj()->setRawData(array($rawData));
	}


}