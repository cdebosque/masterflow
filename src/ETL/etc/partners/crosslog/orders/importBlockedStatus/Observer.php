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

      //$params['filters']['state'] = array("notnull" => true);


      $params['filters']['updated_at'] = array("lt" => date("Y-m-d", strtotime("-2 day")));
      //$params['filters']['entity_id'] = array("gt" => 6371);
      //$params['filters']['increment_id'] = array("in" => array(100015738));
      $params['filters']['is_virtual'] = 0;

      $params['filters']['status'] = array("in" => array(
		'processing', 'logistic_validation', 'logistic_incomplete_order', 'logistic_prepared', 'logistic_packaged'
	  ));

	  /*$params['filters']['state'] = array("in" => array(
		'logistic_packaged'
	  ));*/

      $connector->setMethodparams($params);

      $params = $connector->getMethodparams();
	}

  public function beforeSend(EaiEvent $event) {
    $rawData = $event->getObj()->getRawData();
    self::$incrementId = $rawData['increment_id'];
    //unset($rawData);
    //if (self::$incrementId > 810000) return;

    $order = array('p_CustomerOrderNumber' => self::$incrementId);

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
            . '<p_CustomerOrderNumber>'.self::$incrementId.'</p_CustomerOrderNumber>'


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

    $result = $result["soap:Envelope"]["soap:Body"]["GetCustomerOrderResponse"]["GetCustomerOrderResult"];

    if (!empty($result)) {
		$order = self::convertCrosslogOrder($result);
		self::$connectorIn->getObj()->updateFromCrosslog($order);
	}
  }

}
