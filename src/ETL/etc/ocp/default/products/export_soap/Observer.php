<?php
class Observer extends EaiObserverProduct
{




	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

	    dump(exit);


// 	    $rawData['categories']= self::getArrayIdsForCategoriesPath($rawData['categories']);
// 	    $rawData['categories']= array(15);


	    $sku= $rawData['sku'];
        unset($rawData['sku']);


	    $custEaiData= array($rawData['type']
	                       ,$rawData['attribute_set']
	                       ,$sku
	                       ,(object)$rawData
	                       );




	    $event->getObj()->setRawData($custEaiData);
	}


}