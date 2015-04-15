<?php
class Observer extends EaiObserverCore
{


	public function beforeSend(EaiEvent $event)
    {
        $rawData= $event->getObj()->getRawData();
        dump($rawData);
        $rawData= array($rawData);

        if($event->getObj()->getClass() == "EaiConnectorSoap") {
            $rawData = array($rawData);
        }
        $event->getObj()->setRawData($rawData);
    }

}