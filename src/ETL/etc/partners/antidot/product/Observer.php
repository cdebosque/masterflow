<?php
class Observer extends EaiObserverProduct
{

    static $firstElementFormatted=true;

    static $currentStores=false;

    static $currentWebsites=false;


    public function onConnectInFinish(EaiEvent $event)
    {
        $connector= $event->getObj();

        self::setCategoriesByFullPathMage($connector, null, 1);

        self::setProductSetByKeysMage($connector);

        self::setWebsitesByIdsMage($connector);

        self::setStoresByIdsMage($connector);




        $lang= 'fr_FR';
        if(isset(self::$storesByLocales[$lang])) {
            self::$currentStores=self::$storesByLocales[$lang];
            foreach (self::$currentStores as $store) {
                if(isset(self::$websitesByIds[$store['website_id']])) {
                    self::$currentWebsites[$store['website_id']]= self::$websitesByIds[$store['website_id']];
                }
            }

        }

        if(empty(self::$currentWebsites) or empty(self::$currentStores))
        {

        }


    }


    public function onGetRawFromElementsOutFinish(EaiEvent $event)
    {
        $formatter= $event->getObj();

        $element= $formatter->getElements();

        if( !empty($element) ) {
            $productId= $element['product_id'];
            unset($element['product_id']);


            $element['category_ids']= null;

            $antidotElement= array();
            $antidotElement['identifiers']= array();
            foreach (array('ocp_sku_supplier', 'ocp_ean', 'ocp_product_sku', 'sku') as $elementKey)
            {
                if( isset($element[$elementKey]) )
                {
                    if ($elementKey=='ocp_ean'and 12<=strlen($element[$elementKey]) and strlen($element[$elementKey])<=14) {
                        $antidotElement['identifiers']['gtin']= $element[$elementKey];
                    }
                    else {
                      $antidotElement['identifiers']['identifier'][] = array('@attributes'=>array('type'=>$elementKey),'@value' => $element[$elementKey]);
                    }

                    unset($element[$elementKey]);
                }
            }





            if( !empty($element['website_ids']) ) {
                $antidotElement['websites']= array();
                foreach ($element['website_ids'] as $websiteId) {
                    if(isset(self::$currentWebsites[$websiteId])) {
                        $antidotElement['websites']['website'][]= array('@attributes'=>array('id'=>$websiteId),
                                '@value'=>self::$websitesByIds[$websiteId]['name']
                        );
                    }


                }
                unset($element['website_ids']);
            }


            //             <image_label>BASIFLATSOUP_432_A.jpg</image_label>
            //             <small_image_label>thumb-258_BASIFLATSOUP_432_A.jpg</small_image_label>
            //             <thumbnail_label>thumb-68_BASIFLATSOUP_432_A.jpg</thumbnail_label>

            $antidotElement['name']= self::cleanHtmlText($element['name']);
            $antidotElement['short_name']= self::cleanHtmlText(mb_substr($element['name'], 0,45,'utf-8'));
            unset($element['name']);

            if(!empty($element['thumbnail'])) {
              $antidotElement['url_thumbnail']= ltrim($element['thumbnail'],'/');
              unset($element['thumbnail']);
            }

            if (!empty($element['request_path'])) {
                $antidotElement['url']= $element['request_path'];
                unset($element['request_path']);
            }

            if( !empty($element['ocp_brand'])) {

                if (!empty($element['ocp_brand']['@attributes']['label']) and !empty($element['ocp_brand']['@attributes']['id'])) {
                    unset($element['ocp_brand']['@attributes']['name']);
                    unset($element['ocp_brand']['@attributes']['search']);
                    unset($element['ocp_brand']['@attributes']['sort']);
                    unset($element['ocp_brand']['@attributes']['display_name']);
                    $element['ocp_brand']['@cdata']=$element['ocp_brand']['@attributes']['label'];
                    unset($element['ocp_brand']['@attributes']['label']);
                    $antidotElement['brand']=$element['ocp_brand'];
                }
                unset($element['brand']);
            }

            if( !empty($element['is_in_stock'])) {
                $antidotElement['is_available']= 1;
            } else {
                $antidotElement['is_available']= 0;
            }
            unset($element['is_in_stock']);


            self::setAntidotAllStdFacet($antidotElement, $element, true);

            $antidotElement['marketing']= array();
            //$antidotElement['marketing']['is_featured']= 1;
            foreach (array('ocp_marketing_bestseller', 'ocp_margin_rate', 'is_new') as $elementKey)
            {
                if( !empty($element[$elementKey]) )
                {
                    if( $elementKey=='ocp_marketing_bestseller')
                        $antidotElement['marketing']['is_best_sale']= 1;
                    elseif($elementKey=='ocp_margin_rate')
                        $antidotElement['marketing']['is_boosted']= 1;
                    elseif($elementKey=='is_new')
                        $antidotElement['marketing']['is_new']= 1;
                }

                if(isset($element[$elementKey])) {
                    unset($element[$elementKey]);
                }

            }
            if( empty($antidotElement['marketing']))
                unset($antidotElement['marketing']);

            //Navigation à facette
            self::setAntidotProperties($antidotElement, $element);



            self::setAntidotPrices($antidotElement, $element);


            if (!empty($element['categories'])) {
                $antidotElement['classification']= array();
                foreach ($element['categories'] as $categoryId) {

                    if (!empty(self::$categoriesByIds[$categoryId]) and !empty(self::$categoriesByIds[$categoryId]['tree_info'])) {
                        $classification= self::flatCategoryToTree(self::$categoriesByIds[$categoryId]['tree_info']);
                        if ($classification) {
                            $antidotElement['classification']['category'][]= $classification;
                        }
                    }
                }
                unset($element['categories']);
            }


            if (!empty($element['description']) or !empty($element['short_description'])) {

            /*
                $description= (!empty($element['description'])) ? $element['description'] : $element['short_description'];
                $antidotElement['descriptions']= array( 'description'=> array('@cdata'=>self::cleanHtmlText($description))
                                                        //, 'short_description'=>$element['short_description']
                                                      );
                                                      */

                unset($element['description']);
                unset($element['short_description']);
            }




            $antidotElement['misc']= array();
            foreach ($element as $elementKey=>$elementVal) {
              if( !is_null($elementVal) and !in_array($elementKey, array('configurable_child_ids','configurable_child_attributes')))
                $antidotElement['misc'][$elementKey]= $elementVal;
            }

            if (!empty($element['configurable_child_ids']) and !empty($element['configurable_child_attributes'])) {
                //$antidotElement[]= array();
                foreach ($element['configurable_child_ids'] as $childElements) {
                    $childVariant= array('@attributes'=>array('id'=>$childElements['product_id']));

                    self::setAntidotAllStdFacet($childVariant, $childElements);
                    self::setAntidotProperties($childVariant, $childElements);
                    self::setAntidotPrices($childVariant, $childElements);
//                     $childVariant['misc']= array();
//                     foreach ($antidotElement['misc'] as $elementKey=>$elementVal) {
//                       if ($childElements[$elementKey]!=$elementVal) {
//                         $childVariant['misc'][$elementKey]= $childElements[$elementKey];
//                       }
//                     }
//                     if (empty($childVariant['misc'])) {
//                       unset($childVariant['misc']);
//                     }

                    $antidotElement['variants']['variant'][]= $childVariant;
                }
                unset($element['configurable_child_attributes']);
                unset($element['configurable_child_ids']);
            }


            $custRawData= '';

            //$rootElement=   array('product'=>array('@attributes'=>array('id'=>$element['sku'])));
            $custRawData.= EaiFormatterXml::xmlFromArray($antidotElement, 'product');
            $custRawData= preg_replace('/<product>/', '<product id="'.$productId.'" xml:lang="fr">', $custRawData);


            if( empty($antidotElement['classification'])) {
              $custRawData= false;
            }

            if ($custRawData) {
                if (self::$firstElementFormatted) {
                    $custRawData= '<catalog xmlns="http://ref.antidot.net/store/afs#"><header><owner>OCP</owner><feed>product</feed><generated_at>'.date('c').'</generated_at></header>'
                                  .$custRawData;
                }

                self::$firstElementFormatted= false;
            }

        } else {
            $custRawData= '</catalog>';
        }

        $formatter->connector->setRawData($custRawData);
    }

    function setAntidotPrices(&$antidotElement, &$element)
    {
        $priceAttributes= array('currency'=>'EUR',
                'vat'=>"19.6",
                'vat_included'=>"true",
                'country'=>"FR");



        $priceCutAttributes= $priceAttributes;
        $priceCutAttributes['type']= 'PRICE_CUT';

        $antidotElement['prices']= array();
        $antidotElement['prices']['price'][]= array('@attributes'=>$priceCutAttributes,
                '@value'=>$element['price']
        );


        $priceFinalAttributes= $priceAttributes;
        $priceFinalAttributes['type']= 'PRICE_FINAL';
        if ($element['final_price']<>$element['price']) {
            $priceFinalAttributes['off']= (1-($element['final_price']/$element['price'])*100);
        }

        $antidotElement['prices']['price'][]= array('@attributes'=>$priceFinalAttributes,
                '@value'=>$element['final_price']
        );

        unset($element['formatPrice']);
        unset($element['price']);
        unset($element['final_price']);
        unset($element['min_price']);
        unset($element['max_price']);
        unset($element['minimal_price']);
    }



    function setAntidotProperties(&$antidotElement, &$element)
    {
        $antidotProperties= array();
        foreach ($element as $elementKey=>$elementVal) {
            if( is_array($elementVal) and isset($elementVal['@attributes']) ) {
                $elementAttr= $elementVal['@attributes'];
                if( !empty($elementAttr['label'])) {
                    if (empty($elementAttr['search']) and empty($elementAttr['sort'])) {
                        $element[$elementKey]= $elementAttr['label'];
                        continue;
                    }
                    elseif( !empty($elementAttr['id']) and !empty($elementAttr['label']))
                    {
                        unset($elementAttr['search']);
                        unset($elementAttr['sort']);
                        //                         $elementAttr['display_name']= $elementAttr['label'];
                        $antidotProperties[]= array('@attributes'=>$elementAttr);
                    }
                }
                unset($element[$elementKey]);
            }
        }

        if (!empty($antidotProperties))
            $antidotElement['properties']['property']=$antidotProperties;
    }


    /**
     * Transcodage des facets Magento vers des facets Antidot
     *
     * @param unknown_type $antidotElement
     * @param unknown_type $element
     * @param unknown_type $child
     */
    function setAntidotAllStdFacet(&$antidotElement, &$element, $child=false)
    {
        self::setAntidotStdFacet($antidotElement, $element, 'size', 'ocp_size');
        if( !empty($child) ) {
            self::setAntidotStdFacet($antidotElement, $element, 'color');
            if (!empty($element['ocp_gender']) or !empty($element['ocp_age'])) {
                $antidotElement['audience']= array();
                self::setAntidotStdFacet($antidotElement['audience'], $element, 'gender', 'ocp_gender');
                self::setAntidotStdFacet($antidotElement['audience'], $element, 'age', 'ocp_age');
            }
        }
    }


    function setAntidotStdFacet(&$antidotElement, &$element, $antidotAttr, $mageAttr='')
    {

        if (empty($mageAttr)) {
            $mageAttr= $antidotAttr;
        }
        if (isset($element[$mageAttr]) ) {
            if( is_array($element[$mageAttr]) and isset($element[$mageAttr]['@attributes'])
                    and !empty($element[$mageAttr]['@attributes']['id']) and !empty($element[$mageAttr]['@attributes']['label'])) {
                                $antidotElement[$antidotAttr.'s'][$antidotAttr]= array('@attributes'=>array('id'=>$element[$mageAttr]['@attributes']['id'])
                                                                                       ,'@value'=>$element[$mageAttr]['@attributes']['label']);
            }
            unset($element[$mageAttr]);
        }
    }



    function flatCategoryToTree($fromFlat)
    {
        $srcCat= current($fromFlat);
        array_shift($fromFlat);
        if(!empty($srcCat['id']) and !empty($srcCat['name'])) {
            $nodeTree= array('@attributes'=>array('id'=>$srcCat['id'], 'label'=>$srcCat['name']));
            if(!empty($srcCat['url_rewrite'])) {
              $nodeTree['@attributes']['url']= $srcCat['url_rewrite'];
            }

            if(!empty($fromFlat))
                $nodeTree['category']= self::flatCategoryToTree($fromFlat);
        } else {
            $nodeTree= false;
        }

        return $nodeTree;
    }



    function validateOverXsd($xmlString, $xsdPath)
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        $dom->loadXML($xmlString);
        if ($dom->schemaValidate($xsdPath)) {
            $this->log("schemaValidate() : ok");
            $r = true;
        } else 	{
            dump("schemaValidate() : eaiData no valid for the schema", $xsdPath, 'err');
            //see http://www.php.net/manual/fr/domdocument.schemavalidate.php#62032
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                dump("XSD validation error ".$error->level.": ".trim($error->message), 'err');
            }
            libxml_clear_errors();
        }

        libxml_use_internal_errors(false);
    }

    function cleanHtmlText($string)
    {
        return html_entity_decode($string, ENT_COMPAT | ENT_HTML401, 'UTF-8');
    }



}