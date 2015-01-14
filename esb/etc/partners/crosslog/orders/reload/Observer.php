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

      //$params['filters']['updated_at'] = array("lt" => date("Y-m-d", strtotime("-1 day")));
      ////$params['filters']['increment_id'] = 100027548;
      $params['filters']['increment_id'] = array("in" => array(100025369,100025375));
      $params['filters']['is_virtual'] = 0;

      /*$params['filters']['status'] = array("in" => array(
		'error_crosslog'
	  ));*/

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


    if (is_array($result) && isset($result['soap:Envelope']) && isset($result['soap:Envelope']['soap:Body'])
            && isset($result['soap:Envelope']['soap:Body']['UpdateResponse'])) {
      // Erreur géré par crosslog
      $result = $result['soap:Envelope']['soap:Body']['UpdateResponse']['UpdateResult'];
      if ($result['ErrorCode'] != 0 ) {
        $message = $result['Messages']['AcknowledgmentMessageEntity'];
        $connector->log("Erreur chez crosslog : ".$message['Code'].' '.$message['Message']);
      } else {
        // success mise a jour des status de la commande
        self::$connectorIn->getObj()->updateStatus(self::$incrementId, 'processing', 'logistic_validation');
      }
    } else {
      // Erreur non géré.
      $connector->log("Probleme avec crosslog : ".$result);
    }
  }

}
