<?php
class Observer extends EaiObserverProduct
{
    //public function onFinish_EaiConnectorSoap_connect(EaiEvent $event)
    public function onConnectOutFinish(EaiEvent $event)
    {
        $connector= $event->getObj();
        if ($connector->getClass()=='EaiConnectorSoap') {
            self::setStoresByIdsV1($connector);
        } else {
            self::setStoresByIdsMage($connector);
        }
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

	    if ($event->getObj()->getClass()=='EaiConnectorSoap') {
          $custEaiData= array(array($rawData['attribute_code'], array('label'=>$values)));
	    } else {
    	    $custEaiData= array($rawData['attribute_code'], array('label'=>$values));
	    }

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
	    $mappedCode[1][1]= 'ocp_marketing_axis2';
	    $mappedCode[1][2]= 'ocp_marketing_axis4';
	    $mappedCode[1][3]= 'ocp_matter';
	    $mappedCode[1][4]= 'ocp_cut_type';
	    $mappedCode[1][5]= 'ocp_pattern';
	    $mappedCode[1][7]= 'ocp_marketing_axis3';
	    $mappedCode[1][10]= 'ocp_collection';
	    $mappedCode[3][1]= 'ocp_marketing_axis1';
	    $mappedCode[3][2]= 'ocp_gender';

	    if (isset($element[0]) and isset($element[1]) ) {
	        $rang           = $element[0];
	        $classification = $element[1];

	        if (isset($mappedCode[$rang][$classification])) {
	            $attributeCode = $mappedCode[$rang][$classification];
	        }
	    }

	    return $attributeCode;
	}

}