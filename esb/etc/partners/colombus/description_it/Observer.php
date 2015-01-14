<?php

class Observer extends EaiObserverProduct
{

    static $curProductSku;

    static $newEaiData;


  public function onConnectOutFinish(EaiEvent $event)
  {
    $connector = $event->getObj();

    if ($connector->getClass()=='EaiConnectorMage') {
        self::setStoresByIdsMage($connector);
    } elseif ($connector->getClass()=='EaiConnectorSoap') {
        self::setStoresByIdsV1($connector);
    }
  }

  public function beforeSend(EaiEvent $event) {
    $rawData = $event->getObj()->getRawData();


    if (!empty($rawData['locales'])) {
        $custEaiData = array(array(
            $rawData['ocp_product_sku'],
            $rawData['locales']
        ));
    } else {
        $custEaiData= false;
    }


    $event->getObj()->setRawData($custEaiData);
  }


  public function onMapIn(EaiEvent $event)
  {
      $obj = $event->getObj();

      $eaiData = $obj->getEaiData();


      switch ($eaiData['website_ids']) {
          case 1 : $lang = 'en_US';
          break;
          case 4 : $lang = 'fr_FR';
          break;
          case 5 : $lang = 'es_ES';
          break;
          case 7 : $lang = 'it_IT';
          break;
      }

      $locales= self::getValuesByStoreidKeyVal(array($lang=>array('name'=>utf8_encode(html_entity_decode($eaiData['name'])),
                                                             'description'=>utf8_encode(html_entity_decode($eaiData['description'])),
                                                             'short_description'=>utf8_encode(html_entity_decode($eaiData['description']))
                                                              )),
                                          'store_id',
                                          'attributes');

//       if (is_null(static::$curProductSku)) {
          static::$curProductSku = $eaiData['ocp_product_sku'];
//       }

//       if( empty(static::$newEaiData) ) {
          static::initNewEaiData();
//       }

          static::$newEaiData['locales']= $locales;

//       if (static::$curProductSku !== $eaiData['ocp_product_sku'] ) {
//           static::$curProductSku           = $eaiData['ocp_product_sku'];
          $newEaiData   = static::$newEaiData;
//           static::initNewEaiData();
//       } else {
//           static::$newEaiData['locales']= array_merge(static::$newEaiData['locales'], $locales);
//           $newEaiData           = false;
//       }

//           dump($newEaiData);

      $obj->setEaiData($newEaiData);
  }

  function initNewEaiData()
  {
      static::$newEaiData= array();

      if (!empty(static::$curProductSku)) {
          static::$newEaiData['ocp_product_sku']= static::$curProductSku;
          static::$newEaiData['locales']= array();
      }
  }


}