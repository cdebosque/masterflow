<?php
class Observer extends EaiObserverProduct
{

    public function onConnectOutFinish(EaiEvent $event)
    {
        $connector= $event->getObj();

        self::setProductSetByKeysV2($connector);

        self::setCategoriesByFullPathV2($connector);
    }

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();

// 	    $rawData['categories']= self::getArrayIdsForCategoriesPath($rawData['categories']);
	    $rawData['categories']= array(15);


	    $sku= $rawData['sku'];
        unset($rawData['sku']);


	    $custEaiData= array($rawData['type']
	                       //,self::$productSetByKeys[self::cleanKeyName($rawData['attribute_set'])]
	    ,4
	                       ,$sku
	                       ,$rawData
	                       );

	    dump($custEaiData);
// 	    exit;
	    $event->getObj()->connector->setRawData($custEaiData);
	}


}