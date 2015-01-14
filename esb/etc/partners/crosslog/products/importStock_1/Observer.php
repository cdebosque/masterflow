<?php

class Observer {
  //public function onStart_EaiConnectorSoap_eaiFetchRawData(EaiEvent $event)
  public function onEaiFetchRawDataInStart(EaiEvent $event)
	{
      $connector = $event->getObj();

      $params = $connector->getCallparams();

      //$formatedParams = array($params['action'], array());
      $formatedParams = array($params['action'], array(array('p_ProductReference' => '0000002229782')));

      $connector->setCallparams($formatedParams);
	}

  public function onSoapCall(EaiEvent $event) {
    $connector = $event->getObj();
    $result = $connector->getResults();
//    $result = $result->GetStockImageResult->StockStateEntity;
//    foreach($result as $r) {
//      $connector->setResults(array($r));
//      return;
//    }
    $connector->setResults(array($result->GetStockImageForProductReferenceResult->StockStateEntity));
    unset($result);
  }


    public function beforeSend(EaiEvent $event)
    {
        

        $rawData= $event->getObj()->getRawData(); 
        $custEaiData= array(
            $rawData['id'],
            
                array('qty' => $rawData['qty'],
                    'stock_id' => 1,
                    'identified_by' => 'ocp_ean'));
        // c'est le tableau que tu va passer a la methode de l'api
        dump($custEaiData);
        $event->getObj()->setRawData($custEaiData);
    }
 }