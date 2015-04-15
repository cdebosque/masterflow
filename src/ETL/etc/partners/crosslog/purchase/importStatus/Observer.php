<?php

class Observer Extends EaiObserverCrosslog {

  static $connectorIn = null;
  static $pOrderId = '';

  public function onConnectInFinish(EaiEvent $event){
    self::$connectorIn = $event->getObj();
  }

  public function onFetchRawDataInStart(EaiEvent $event)
	{
      $connector = $event->getObj();

      $params = $connector->getMethodparams();

      $params['filters']['po_status'] = 'waiting_for_delivery';
      //$params['filters']['po_order_id'] = 'WESC-001078464';
      //$params['filters']['po_supply_date'] = array("lt" => date("Y-m-d"));
      $params['store'] = null;
      $params['withProducts'] = false;

      $connector->setMethodparams($params);
	}

   public function beforeSend(EaiEvent $event) {
    $rawData = $event->getObj()->getRawData();
    self::$pOrderId = $rawData['po_order_id'];


    $requestXml = EaiFormatterXml::xmlFromArray(self::$pOrderId, 'p_SupplyOrderNumber');

    $params = $event->getObj()->getCallparams();
    $header = $event->getObj()->getHeader();
    self::$username = $header['params']['Username'];
    self::$password = $header['params']['Password'];

    $requestXml = '<SOAP-ENV:Header>'
            . self::getXmlHeaderAuth() . '
                  </SOAP-ENV:Header>
                  <SOAP-ENV:Body>
                  <' . $params['action'] . ' xmlns="http://ws.crossdesk.com">'
            . $requestXml
            . '</' . $params['action'] . '>
                  </SOAP-ENV:Body>
                ';

    $event->getObj()->setXmlRequest($requestXml);
  }

  public function onSoapWrite(EaiEvent $event) {
    $connector = $event->getObj();
    $result = $connector->getResults();

    if (is_array($result) && isset($result['soap:Envelope']) && isset($result['soap:Envelope']['soap:Body'])
            && isset($result['soap:Envelope']['soap:Body']['GetSupplyOrderEventsResponse'])) {
      // Erreur géré par crosslog
      $result = $result['soap:Envelope']['soap:Body']['GetSupplyOrderEventsResponse']['GetSupplyOrderEventsResult'];
      if (isset($result['SupplyOrderEventEntity'])) {
		$nbEvent = count($result['SupplyOrderEventEntity']);
		$lastEvent = $result['SupplyOrderEventEntity'][$nbEvent - 1];
        self::$connectorIn->getObj()->updateStatus(self::$pOrderId, 
        self::convertCrosslogSupplyOrderStatus($lastEvent["SupplyOrderState"]));
      } else {
        $connector->log(self::$pOrderId." pas d'evenement sur la commande : ".$result);
      }
    } else {
      // Erreur non géré.
      $connector->log(self::$pOrderId." Probleme avec crosslog : ".$result);
    }
  }
 }
