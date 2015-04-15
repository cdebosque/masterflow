<?php
class Observer extends EaiObject
{
    static $newElements = array();

    static $currentModel= false;

    static $pbwebsiteId= array('PVEUF'=>1,
                             'PVEUE'=>4,
                             'PVEUI'=>5,
                             'PVEUB'=>6,
                             'PVSUI'=>7
                            );


	public function beforeSend(EaiEvent $event)
	{
		$rawData = $event->getObj()->getRawData();

		if (isset(static::$pbwebsiteId[$rawData['website_id']])) {
    		$websiteId= static::$pbwebsiteId[$rawData['website_id']];
		} else {
		    $websiteId= 0;
		}

		//Mode simle sans mise à jour des matrices
		$useSuperPrice= false;

		$currentRuptOn= $rawData['ocp_product_sku'].':'.$rawData['ocp_color'];

		if(true) {
		    if(static::$currentModel===false ) {
		        static::$currentModel=  $currentRuptOn;
		    }

		    if( $currentRuptOn == static::$currentModel )
		    {
		        static::$currentModel=  $currentRuptOn;
		        static::$newElements[$websiteId][$rawData['sku']]=$rawData['price'];
		    } else {
		        if( count(static::$newElements) > 1 ) {
		            foreach(static::$newElements as $websiteId=>$storePrices) {
		                if (count($storePrices)>1 ) {
		                    $configurableSku= str_replace(':','\\', static::$currentModel);
        		            $configurablePrice= false;
    		                foreach($storePrices as $sku=>$price) {
    		                    $configurablePrice= ($configurablePrice===false) ? $price : min($configurablePrice,$price);
    		                }
    		                static::$newElements[$websiteId][$configurableSku]= $configurablePrice;
		                }
		            }
		        }

		        $custEaiData['store_id']=static::$newElements;
		        static::$currentModel=  $currentRuptOn;
		    }


		} elseif (!$useSuperPrice) {
		    $custEaiData = array();

		    if( !empty($rawData['sku']) ) {
		        $custEaiData['searchBy']= array('sku' => $rawData['sku']);
		    } else {
		        if( !empty($rawData['ocp_color'])) {
		            $colorCode= $rawData['ocp_color'];
        		    //"MULTICOLORE"=>"MULT",
        		    if( $colorCode =="MULT" ) {
        		        $custEaiData['searchBy']= array('sku' => $rawData['model'].'\\1');
        		    } else {
        		        $custEaiData['searchByAlternate']= array('ocp_product_sku' => $rawData['ocp_product_sku'], 'ocp_color' => $colorCode);
        		    }

		        } else {
		            $custEaiData['searchByAlternate']= array('ocp_product_sku' => $rawData['ocp_product_sku']);
		        }
		    }

		    $custEaiData['price']= $rawData['price'];

		} else {

    		if( empty($rawData['sku']) ) {
    		    $custEaiData = array();
    		    //$custEaiData['sku']= $rawData['ocp_product_sku'].'\1';
    		    $custEaiData= $rawData;
    		    $custEaiData['type']= 'simple';
    		} else {

        		if(static::$currentModel===false ) {
        		    static::$currentModel=  $currentRuptOn;
        	    }

        		if( $currentRuptOn == static::$currentModel )
        		{
        		    static::$currentModel=  $currentRuptOn;
        		    static::$newElements[]= $rawData;
        		    $custEaiData = false;
        		} else {
        		    $custEaiData = array();
        		    $custEaiData= static::$newElements[0];
        		    if( static::$newElements > 0 ) {
        		        $custEaiData['type']= 'configurable';
        		        $custEaiData['simples_skus']= array();
        		        $custEaiData['sku']= $custEaiData['ocp_product_sku'].'\\'.$custEaiData['ocp_color'];
                        $price= $custEaiData['price'];

        		        foreach(static::$newElements as $element) {
        		            $price= min($price,$element['price']);
        		        }
        		        $custEaiData['price']= $price;
        		        foreach(static::$newElements as $element) {
        		            $custEaiData['simples_skus'][$element['sku']]= array('sku'=>$element['sku'], 'pricing_value'=>$element['price']-$price, 'real_price'=>$element['price']);
        		        }

        		    } else {
        		        $custEaiData['type']= 'simple';
        		    }

        		    static::$newElements= array();
        		    static::$newElements[]= $rawData;
        		    static::$currentModel=  $currentRuptOn;
        		}
    		}
		}


		if( $custEaiData) {

// 		    $custEaiData['store_id']= $websiteId;


		    $event->getObj()->log('send price for: '.$rawData['ocp_product_sku'].' and websiteId:'.$websiteId);


		    if( !$useSuperPrice ) {
		        $custEaiData = array($custEaiData);
		    } else {
    		    $sku = $custEaiData['sku'];
    		    unset($custEaiData['sku']);

        		$custEaiData = array(array($sku
        		                   , $custEaiData)
        		);

		    }

		    dump($custEaiData);
		}

		$custEaiData= false;
		$event->getObj()->setRawData($custEaiData);
	}


}//class