<?php

class Observer Extends EaiObserverCrosslog {

  static $connectorIn = null;
  static $incrementId = '';

  public function onConnectInFinish(EaiEvent $event){
    self::$connectorIn = $event->getObj();
  }

  public function onFetchRawDataInStart(EaiEvent $event)
	{
	  $connector = $event->getObj();

	  $params = $connector->getMethodparams();

	  $params['filters']['created_at'] = array("gt" => date("Y-m-d", strtotime("-10 day")));;

	  $connector->setMethodparams($params);

	  $params = $connector->getMethodparams();
	}

  public function beforeSend(EaiEvent $event) {
    $rawData = $event->getObj()->getRawData();
    self::$incrementId = $rawData['increment_id'];

    $order = self::makeCrosslogOrder($rawData);

    $requestXml = EaiFormatterXml::xmlFromArray($order, 'p_CustomerOrder');

    $params = $event->getObj()->getCallparams();
    $header = $event->getObj()->getHeader();
    self::$username = $header['params']['Username'];
    self::$password = $header['params']['Password'];

    $requestXml = '<SOAP-ENV:Header>'
            . self::getXmlHeaderAuth($user, $password) . '
                  </SOAP-ENV:Header>
                  <SOAP-ENV:Body>
                  <' . $params['action'] . ' xmlns="http://ws.crossdesk.com">'
            . $requestXml
            . '</' . $params['action'] . '>
                  </SOAP-ENV:Body>
                ';
//    // Vérification de l'existence de la commande coté Crosslog
//    $existRequest = self::getOrderExistsRequest($order['Number']);
//    $event->getObj()->setXmlRequest($existRequest);
//    $result = $event->getObj()->doRequest();
//    dump($result);
    
    $event->getObj()->setXmlRequest($requestXml);
  }



  public function onSoapWrite(EaiEvent $event) {
    $connector = $event->getObj();
    $result = $connector->getResults();
    
    
    $result = self::crosslogErrorHandler($result, 'Create', self::$incrementId, -3,true);
    if ($result['succes']) {
        self::$connectorIn->getObj()->updateStatus(self::$incrementId, 'processing', 'logistic_validation');
    } else {
        self::$connectorIn->getObj()->updateStatus(self::$incrementId, 'new', 'error_crosslog');
    }
    $connector->log($result['msg']);
    
  }

}
