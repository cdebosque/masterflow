<?php

class Observer Extends EaiObserverCrosslog {

  public function beforeSend(EaiEvent $event) {

    $rawData = $event->getObj()->getRawData();
   
    

    $order = self::makeCrosslogSupplyOrder($rawData);
    


    $requestXml = EaiFormatterXml::xmlFromArray($order, 'p_SupplyOrder');



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
    
    $log = '';

    
    if (is_array($result) && isset($result['soap:Envelope']) && isset($result['soap:Envelope']['soap:Body'])
            && isset($result['soap:Envelope']['soap:Body']['CreateResponse'])) {
      // Erreur géré par crosslog
      $result = $result['soap:Envelope']['soap:Body']['CreateResponse']['CreateResult'];
      if ($result['ErrorCode'] != 0 ) {
		//var_dump($message);
        $message = $result['Messages']['AcknowledgmentMessageEntity'];
        if (!isset($message['Message'])) {
            //foreach ($result['Messages']['AcknowledgmentMessageEntity'] as $tmp)
                //$message .= ' '.$tmp['Message'];
            //var_dump($message);
        }
        $log .= "Erreur chez crosslog : ".$message['Code'].' '.$message['Message'];
      } else {        
        // success mise a jour des status de la commande
        $log .= " succes";
        //self::$connectorIn->getObj()->updateStatus(self::$incrementId, 'new', 'logistic_validation');
      }
    } else {
      // Erreur non géré.
       $log .= " Erreur XML avec crosslog : ".print_r($result,1);
    }
    $connector->log($log);
  }

}
