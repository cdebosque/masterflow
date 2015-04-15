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

        // HACK OCP attention l'espace apres le & 2) est important
        $params['filters']['(ocp_esb_flag & 2) '] = 0;
        //$params['filters']['po_status'] = array("neq" => 'complete');
        $params['filters']['po_delivery_percent'] = array("lt" => 100);
        $params['filters']['po_supply_date'] = array("lt" => date("Y-m-d"));
        $params['store'] = null;
        $params['withProducts'] = false;
        //$params['limit'] = 20;

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
            && isset($result['soap:Envelope']['soap:Body']['GetSupplyOrderResponse'])) {
            // Erreur géré par crosslog
            $result = $result['soap:Envelope']['soap:Body']['GetSupplyOrderResponse']['GetSupplyOrderResult'];
            if ($result['ErrorCode'] != 0 ) {
                //$connector->log(self::$incrementId." Erreur chez crosslog : ".$message['Code'].' '.$message['Message']);
            } else {
                // success mise a jour des status de la commande
                self::$connectorIn->getObj()->update(self::$pOrderId,
                    self::convertCrosslogSupplyOrder($result));
            }
        } else {
            // Erreur non géré.
            $connector->log(self::$pOrderId." Probleme avec crosslog : ".print_r($result, true));
        }
    }
}
