<?php
class Observer extends EaiObserverProduct
{
	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    if ($event->getObj()->getClass()=='EaiConnectorMage') {
	        $custEaiData = array(
	                $rawData['ocp_product_sku'],
	                array('special_price'=>$rawData['special_price'])
	        );
	    } else {
	        $custEaiData = array(array(
	                $rawData['ocp_product_sku'],
	                array('special_price'=>$rawData['special_price'])
	        ));
	    }


	    $event->getObj()->setRawData($custEaiData);
	}
}