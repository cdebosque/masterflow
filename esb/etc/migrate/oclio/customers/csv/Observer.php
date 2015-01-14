<?php



class Observer extends EaiObject
{

  public function onMapIn(EaiEvent $event)
	{
	  $eaiData= $event->getObj()->getProp('eaiData');

		if(!isset($eaiData['customers_dob'])){
			$eaiData['customers_dob'] = dateday();
		}
    
		$event->getObj()->setProp('eaiData', $eaiData);
	}

  
  public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();
	    $rawData= array($rawData);
	    $event->getObj()->setRawData($rawData);
	}


}