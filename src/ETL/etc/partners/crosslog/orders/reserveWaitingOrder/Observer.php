<?php

class Observer Extends EaiObserverCrosslog {

  static $connectorIn = null;
  static $incrementId = '';

  public function onConnectInFinish(EaiEvent $event){
    self::$connectorIn = $event->getObj();
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
    
    $log = self::$incrementId.' - ';

    
    if (is_array($result) && isset($result['soap:Envelope']) && isset($result['soap:Envelope']['soap:Body'])
            && isset($result['soap:Envelope']['soap:Body']['CreateResponse'])) {
      // Erreur géré par crosslog
      $result = $result['soap:Envelope']['soap:Body']['CreateResponse']['CreateResult'];
      if ($result['ErrorCode'] != 0 ) {
        $message = $result['Messages']['AcknowledgmentMessageEntity'];
        if (!isset($message['Message'])) {
            //foreach ($result['Messages']['AcknowledgmentMessageEntity'] as $tmp)
                //$message .= ' '.$tmp['Message'];
            var_dump($message);
        }
        mail('jaymard@oclio.com' , 'erreur integration commande dpam '.self::$incrementId , $message['Message']);
        $log .= "Erreur chez crosslog : ".$message['Code'].' '.$message['Message'];
      } else {        
        // success mise a jour des status de la commande
        $log .= " succes";
        self::$connectorIn->getObj()->updateStatus(self::$incrementId, 'new', 'waiting_authorization_reserved');
      }
    } else {
      // Erreur non géré.
      mail('jaymard@oclio.com' , 'erreur integration commande dpam '.self::$incrementId , $result);
       $log .= " Erreur XML avec crosslog : ".$result;
    }
    $connector->log($log);
  }

}
