<?php

class Observer Extends EaiObserverCrosslog {

  public function beforeSend(EaiEvent $event) {
    $rawData = $event->getObj()->getRawData();

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

}