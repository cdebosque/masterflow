<?php

class Observer Extends EaiObserverCrosslog {
  static $flagFile = 'etc/partners/crosslog/products/exportNew/eanFlagFile.txt';
  static $handle = null;
  static $nb = 0;

  public function beforeSend(EaiEvent $event) {
    $connector = $event->getObj();

    $rawData = $connector->getRawData();

    //var_dump($rawData);exit();
    if (in_array($rawData['ean'], self::$customArray)) {
      $connector->log($rawData['ean'].' déjà exporté.');
      $event->getObj()->setXmlRequest(null);
      return true;
    } else {
      fwrite(self::$handle, $rawData['ean']."\n");
      $connector->log($rawData['ean'].' à traiter ...');
    }
    
    $rawData = array(
        'Label' => substr($rawData['ref'].' - '.$rawData['couleur'].' - '.$rawData['taille'],0 ,80),
        'Reference' => $rawData['ean'],
        'Ean13' => $rawData['ean'],
        'Type' => 'P');

    //$rawData = 'OC-0000-0001';

    $requestXml = EaiFormatterXml::xmlFromArray($rawData, 'p_Product');

    //dump($requestXml);exit();

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
    self::$nb++;
    if (self::$nb%10 == 0)
        echo '.';
    if ($result === true) {
      return;
    }
    if (is_array($result) && isset($result['soap:Envelope']) && isset($result['soap:Envelope']['soap:Body'])
            && isset($result['soap:Envelope']['soap:Body']['CreateResponse'])) {
      // Erreur géré par crosslog
      $result = $result['soap:Envelope']['soap:Body']['CreateResponse']['CreateResult'];
      if ($result['ErrorCode'] != 0 ) {
        $message = $result['Messages']['AcknowledgmentMessageEntity'];
        $connector->log("Erreur chez crosslog : ".$message['Code'].' '.$message['Message']);
      }
    } else {
      // Erreur non géré.
      $connector->log("Probleme avec crosslog : ".print_r($result,1));
    }
  }

  public function onConnectOutFinish(EaiEvent $event) {
    $connector = $event->getObj();
    self::setCustomArrayFromFile(self::$flagFile);
    self::$handle = fopen(self::$flagFile, 'a');
  }

}
