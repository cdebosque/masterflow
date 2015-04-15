<?php
class Observer extends EaiObserverProduct
{
	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();



	    $newRawData = array('category_id'  => $rawData['category_id']);

        $newRawData['searchBy']= array();
        $newRawData['searchByAlternate']= array('ocp_product_sku' => $rawData['ocp_product_sku'],
                                                'visibility'=>4
                );

        if( !empty($rawData['ocp_color'])) {
            $newRawData['searchByAlternate']['ocp_color']= $rawData['ocp_color'];
        }


        $newRawData= array($newRawData);

	    $event->getObj()->setRawData($newRawData);
	}
}