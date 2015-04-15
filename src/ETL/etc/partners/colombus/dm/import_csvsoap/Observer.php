<?php
class Observer extends EaiObserverProduct
{
    //public function onFinish_EaiConnectorSoap_connect(EaiEvent $event)
    public function onConnectOutFinish(EaiEvent $event)
    {
        $connector= $event->getObj();

        self::setStoresByIdsV1($connector);
    }

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    $values= array();
	    $values[]= array('store_id'=>0, 'value'=>$rawData['store_id']['default']);
	    foreach (self::$storesByLocales as $storeLocale=>$storeValues) {
	        if (isset($rawData['store_id'][$storeLocale])) {
	            foreach ($storeValues as $store) {
    	            $values[]= array('store_id'=>$store['store_id']
    	                            ,'value'=>$rawData['store_id'][$storeLocale]);
	            }
	        }
	    }

        $custEaiData= array(array($rawData['attribute_code'], array('label'=>$values)));
	    $event->getObj()->setRawData($custEaiData);
	}


	public function beforeMap(EaiEvent $event)
	{
	    $element= $event->getObj()->getElement();

	    $attributeCode= self::getAttributeCodeByRangAndClassification($element);

	    if ($attributeCode) {
            $element['attribute_code']= $attributeCode;
	    } else {
	        $element= false;
	    }

        $event->getObj()->setElement($element);
	}


	function getAttributeCodeByRangAndClassification($element)
	{
	    $attributeCode= false;

	    $mappedCode= array();
	    $mappedCode[1]= 'ocp_size_code';
	    $mappedCode[2]= 'ocp_color';

	    if (isset($element[0])) {
	        $classification = $element[0];

	        if (isset($mappedCode[$classification])) {
	            $attributeCode = $mappedCode[$classification];
	        }
	    }

	    return $attributeCode;
	}

}