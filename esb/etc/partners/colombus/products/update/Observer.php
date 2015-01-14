<?php
class Observer extends EaiObserverProduct
{
    static $complexProduct=array();

    static $configurableProductsData=array();

    static $configurableAttribute= array(//'color_code',
                                         'ocp_size_code',
                                        );


    static $updateSpecificAttributes= array('attribute_code','color','status','visibility','enable_googlecheckout',
                                        'tax_class_id','default_supply_delay','ocp_cut_type','ocp_fixation_base',
                                        'ocp_gender','ocp_machine_wash','ocp_marketing_axis2','ocp_marketing_axis3',
                                        'ocp_marketing_axis4','ocp_play_arch','ocp_season','ocp_seat_tilting',
                                        'ocp_shield','ocp_size_code','ocp_supply','ocp_trucker_all','ocp_color');



	public function beforeSend(EaiEvent $event)
	{
		$rawData = $event->getObj()->getRawData();

		$sku = $rawData['sku'];
		unset($rawData['sku']);

        if (!empty($rawData['ocp_size'])) {
            $sizeList= explode(',', $rawData['ocp_size']);
            $rawData['ocp_size']= $sizeList;
        } else {
            $rawData['ocp_size']= array('Defaut');
        }

        if (!empty($rawData['ocp_matter'])) {
            $rawData['ocp_matter']= array($rawData['ocp_matter']);
        }


        if (empty($rawData['color'])) {
            $rawData['color']= 'Autres';
        } else {
            $colorList= explode(',', $rawData['color']);
            $rawData['color']= $colorList[0];
        }


        if (!isset($rawData['tax_class_id'])) {
            $rawData['tax_class_id']= 2;
        }

		$rawData['visibility']= 1;


		foreach($rawData as $rawKey=>$rawValue)
		{
		    if (!in_array($rawKey,self::$allowProductV2Attributes, true)) {
		        if (is_array($rawValue)) {
		            $rawData['additional_attributes']['multi_data'][]= array('key'=>$rawKey, 'value'=> $rawValue);
		        } else {
		            $rawData['additional_attributes']['single_data'][]= array('key'=>$rawKey, 'value'=> $rawValue);
		        }
		        unset($rawData[$rawKey]);
		    }
		}

		$custEaiData = array( $sku
		                    , $rawData
		);

		$event->getObj()->setRawData($custEaiData);
	}



	public function onFetchElementsInFinish(EaiEvent $event)
	{
	    $sequencedElements = $event->getObj()->getElements();

	    if (isset($sequencedElements[0]) && is_array($sequencedElements[0])) {
	        $sequencedElement  = $sequencedElements[0];

	        $newTmpElements = array();

	        $commonAttributes = array('type'   => "simple");

	        $countFA= 0;
	        if (isset($sequencedElement['FA']) && is_array($sequencedElement['FA'])) {

	            //On ne prend que les produits valides
	            // 			    if( !self::isValidOcpSkuModel($sequencedElement['FA'][0][1]) ) {
	            //     			    $event->getObj()->log('skipping model:'.$sequencedElement['FA'][0][1]);
	            // 			        $event->getObj()->setElements(array());
	            // 			        return false;
	            // 			    }

	            if ( count($sequencedElement['FA'])>1 ) {
	                $countFA= count($sequencedElement['FA']);
	                $commonAttributes['type']= $countFA;
	            }

	            foreach ($sequencedElement['FA'] as $index => $fa) {
	                $newTmpElements[$index] = array_merge($commonAttributes, $fa);
	            }
	        } else {
	            return false;
	        }

	        //Tri des produits par couleur et positionnement du split
	        usort($newTmpElements, 'self::sortByColorOn37');

	        $colorAggregation= false;
	        $newElements = array();
	        $countTmpElements  =0;
	        $countConfigProduct=0;
	        foreach ($newTmpElements as $elemntKey=>$elementVal) {
	            $simpleAdded= false;
	            $countTmpElements++;
	            $colorCode= $elementVal[37];

	            if (empty($colorAggregation)) {
	                $colorAggregation= $colorCode;
	            }

	            //On ajoute le produit configurable
	            if (($colorAggregation!==$colorCode or $countTmpElements==$countFA) and is_int($elementVal['type'])) {
	                $countConfigProduct++;
	                $elementValConfig= end($newElements);
	                $elementValConfig['type']  = 'configurable';
	                $elementValConfig['weight']= 1;
	                //Gestion du SKU
	                $elementValConfig[35]      = preg_replace('/\d+$/',$colorAggregation,$elementValConfig[35]);
	                //Gestion de EAN
	                $elementValConfig[36]      = $elementValConfig[36].str_pad($countConfigProduct, 5, '0', STR_PAD_LEFT);

	                //Le dernier element de la liste doit être ajouté avant son produit configurable
	                if ($countTmpElements==$countFA) {
	                    $newElements[] = $elementVal;
	                    $simpleAdded= true;
	                }
	                $newElements[] = $elementValConfig;
	                $colorAggregation= $colorCode;
	            }

	            //On n'est pas sur le dernier element donc on ajoute le produit simple
	            if (!$simpleAdded) {
	                $newElements[] = $elementVal;
	            }
	        }

	        //dump($newElements);

	        $event->getObj()->setElements($newElements);
	    }

	}//function


	public function sortByColorOn37($a, $b)
	{
	    return strcmp($a[37],$b[37]);
	}
}//class