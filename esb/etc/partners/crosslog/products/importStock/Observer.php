<?php

class Observer {
  static $reportFile = '';
  static $handle = null;
  static $nb = 0;
  static $txtLog = '';
  
  public function onConnectOutFinish(EaiEvent $event) {

    $archiveName = Esb::ROOT.'/var/reports/crosslog/stock_'.date('Ymd', strtotime("-1 day")).'.csv';
    $deleteName = Esb::ROOT.'/var/reports/crosslog/stock_'.date('Ymd', strtotime("-30 days")).'.csv';
    self::$reportFile = Esb::ROOT.'/var/reports/crosslog/stock.csv';

    rename(self::$reportFile, $archiveName);
    unlink($deleteName);

    self::$handle = fopen(self::$reportFile, 'w');
    if (self::$handle !== false)
      fwrite(self::$handle, 'AvailableStockQuantity;Ean13;InsulatedStockQuantity;ReservedStockQuantity;StockQuantity'."\n");
 
  }

  public function onDisconnectOutStart(EaiEvent $event)
  {
        /* @var $connector EaiConnectorMage */
        $connector = $event->getObj();

        if ($connector->getClass() == 'EaiConnectorSoap') {
            $soapClient = $connector->getClient();
            $result = $soapClient->call($connector->getSession(), 'erp_stock.updateFinish');
        } elseif ($connector->getClass() == 'EaiConnectorMage') {
            /* @var $mageObj MDN_AdvancedStock_Model_CatalogInventory_Stock_Item_Api */
            $mageObj = $connector->getObj();
            $mageObj->updateFinish(1);
        }
        if (self::$handle !== false)
          fclose(self::$handle);
  }

  public function onEaiFetchRawDataInStart(EaiEvent $event)
	{
      $connector = $event->getObj();

      $params = $connector->getCallparams();

      $formatedParams = array($params['action'], array());

      $connector->setCallparams($formatedParams);
	}

  public function onSoapCall(EaiEvent $event) {
    $connector = $event->getObj();
    $result = $connector->getResults();
//    $result = $result->GetStockImageResult->StockStateEntity;
//    foreach($result as $r) {
//      $connector->setResults(array($r));
//      return;
//    }
    $connector->setResults($result->GetStockImageResult->StockStateEntity);
    unset($result);
  }

  public function beforeSend(EaiEvent $event) {
    $rawData = $event->getObj()->getRawData();

    if (self::$handle !== false)
      fwrite(self::$handle, implode(';' , $rawData)."\n");
    $custData = array(
      'id' => $rawData["sku"],
      'data' => array(
        'stock_id' => 1,
        'identified_by' => 'ocp_ean',
        'qty' => (int)$rawData["qty"]
      )
    );

    self::$nb++;
    if (self::$nb % 20 == 0) self::$txtLog = self::$txtLog.'.';
    if (self::$nb % 1000 == 0) {
      echo self::$txtLog." ".self::$nb.' '.date('H:i:s').PHP_EOL;
      self::$txtLog = '';
    }

    if ((int)$rawData["qty"] == 0) $custData = false;
    $event->getObj()->setRawData($custData);
    unset($rawData);
  }

 }
