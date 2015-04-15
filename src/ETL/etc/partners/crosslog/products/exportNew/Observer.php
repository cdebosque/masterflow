<?php

class Observer Extends EaiObserverCrosslog {
  static $flagFile = '/filer/www/dpam.com/httpdocs/shell/esb/etc/partners/crosslog/products/exportNew/eanFlagFile.txt';
  static $handle = null;
  static $compteur = 0;

  public function beforeSend(EaiEvent $event) {
	  
	self::$compteur++;
	echo '.';
	
	if (self::$compteur % 100 == 0) {
		echo " - ".self::$compteur."\n";
	}
	
	
    
    $rawData = $event->getObj()->getRawData();
    
   
    //var_dump($rawData);exit();
    if (in_array($rawData['ocp_ean'], self::$customArray)) {
      $connector = $event->getObj();
      $connector->log($rawData['ocp_ean'].' déjà exporté.');
      $event->getObj()->setXmlRequest(null);
      return true;
    } else {
      fwrite(self::$handle, $rawData['ocp_ean']."\n");
    }
    
    $rawData = array(
        'Label' => substr($rawData['name'].' - '.$rawData['ocp_color_value'].' - '.$rawData['ocp_size_code_value'],0 ,80),
        'Reference' => $rawData['ocp_ean'],
        'Ean13' => $rawData['ocp_ean'],
        'Type' => 'P');
        
    //var_dump($rawData);

    //$rawData = 'OC-0000-0001';

    $requestXml = EaiFormatterXml::xmlFromArray($rawData, 'p_Product');

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
    if ($result === true) return;
    if (is_array($result) && isset($result['soap:Envelope']) && isset($result['soap:Envelope']['soap:Body'])
            && isset($result['soap:Envelope']['soap:Body']['CreateResponse'])) {
      // Erreur géré par crosslog
      //var_dump($result);
      $result = $result['soap:Envelope']['soap:Body']['CreateResponse']['CreateResult'];
      if ($result['ErrorCode'] != 0 ) {
        $message = $result['Messages']['AcknowledgmentMessageEntity'];
        $connector->log("Erreur chez crosslog : ".$message['Code'].' '.$message['Message']);
      }
    } else {
      // Erreur non géré.
      $connector->log("Probleme avec crosslog : ".print_r($result, 1));
      var_dump($result);
    }
  }

  public function onConnectOutFinish(EaiEvent $event) {
    $connector = $event->getObj();
    echo "lecture fichier ean \n";
    self::setCustomArrayFromFile(self::$flagFile);
    self::$handle = fopen(self::$flagFile, 'a');
  }

}
