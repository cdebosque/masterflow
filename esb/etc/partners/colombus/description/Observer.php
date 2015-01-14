<?php

class Observer extends EaiObserverProduct
{

  static $curProductSku;

  static $newEaiData;

  static $lastRawData;

  static $lastLineHacked = false;


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
        $custEaiData = array(
            $rawData['ocp_product_sku'],
            $rawData['locales']
        );
        if($event->getObj()->getClass() == "EaiConnectorSoap") {
        	$custEaiData = array($custEaiData);
        }
    } else {
        $custEaiData= false;
        $event->getObj()->log('locales for :'.$rawData['ocp_product_sku'], 'error');
    }

    $event->getObj()->setRawData($custEaiData);
  }


  public function onMapIn(EaiEvent $event)
  {
    /* @var $obj EaiMapper */
      $obj = $event->getObj();

    	//$obj->log('onMapIn start - Memory used : ' . number_format(memory_get_usage(), 0, ',', ' ') . ' octets.');
    
      $eaiData = $obj->getEaiData();
      $newEaiData= false;

      //if (in_array($eaiData['website_ids'], array('fr_FR','es_ES','en_US'))) {
      // @TODO : Faire une fonction dans EaiObserverCore.php du style 
      // if (isLocaleExists($eaiData['website_ids'])) {
      if (isset(self::$storesByLocales[$eaiData['website_ids']])) {

          $lang = $eaiData['website_ids'];
          $locales= self::getValuesByStoreidKeyVal(array($lang=>array('name'      => static::formatText($eaiData['name']),
                                                                 'description'      => static::formatText($eaiData['description']),
                                                                 'short_description'=> static::formatText($eaiData['description'])
                                                                  )),
                                              'store_id',
                                              'attributes');

          //Premier passage -- inutile car (null !== first_ocp_product_sku)
          //if (is_null(static::$curProductSku)) {
          //    static::$curProductSku = $eaiData['ocp_product_sku'];
          //}

          if (empty(static::$newEaiData)) {
              static::initNewEaiData();
          }

          if (static::$curProductSku !== $eaiData['ocp_product_sku'] or static::$lastLineHacked === true) {
              static::$curProductSku = $eaiData['ocp_product_sku'];
              $newEaiData   = static::$newEaiData;
              static::initNewEaiData();
              static::$newEaiData['locales']= $locales;
          } else {
              static::$newEaiData['locales']= array_merge(static::$newEaiData['locales'], $locales);
              $newEaiData           = false;
          }

      }
      else {
        $obj->log('Locale "'.$eaiData['website_ids'].'" unknown', 'err');
      }

      $obj->setEaiData($newEaiData);

      //$obj->log('onMapIn finish - Memory used : ' . number_format(memory_get_usage(), 0, ',', ' ') . ' octets.');
  }


  function formatText($char)
  {
      $formattedText= $char;
      //$formattedText= utf8_encode($formattedText);
      //$formattedText= html_entity_decode($formattedText);
      //$formattedText= utf8_encode($formattedText);

      return $formattedText;
  }

  function initNewEaiData()
  {
      static::$newEaiData= array();

      if (!empty(static::$curProductSku)) {
          static::$newEaiData['ocp_product_sku']= static::$curProductSku;
          static::$newEaiData['locales']= array();
      }
  }
  
  function onFetchRawDataInFinish(EaiEvent $event)
  {
    $obj = $event->getObj();

    $rawData = $obj->getRawData();

    if ($rawData === false && !static::$lastLineHacked) {
      $obj->setRawData(static::$lastRawData);
      static::$lastLineHacked = true;
    }
    elseif ($rawData !== false && isset($rawData[0])) {
      static::$lastRawData = $rawData;
    }
  }

  function onConnectorFileEOF(EaiEvent $event)
  {
    $obj = $event->getObj();
    
    if ($obj->getProp('tmpLines')) {
      $obj->setProp('tmpLines', array());
    } else {
      $obj->setProp('tmpLines', $obj->getRawData());
    }    
  }
  
}
