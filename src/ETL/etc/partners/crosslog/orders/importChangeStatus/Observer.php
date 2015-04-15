<?php

class Observer {

	//public function onStart_EaiConnectorSoap_eaiFetchRawData(EaiEvent $event)
    public function onEaiFetchRawDataInStart(EaiEvent $event)
	{
	    /*$rawData= $event->getObj()->getRawData();

	    //$custEaiData= array($rawData['attribute_code'],$rawData);
	    $custEaiData= array(array($rawData));*/
      $connector = $event->getObj();

      $params = $connector->getCallparams();

      $param = new stdClass();
      $param->p_CustomerOrderNumber = $params['args']['p_CustomerOrderNumber'];
      $formatedParams = array(
          $params['action'],
          array($param)
      );
      $connector->setCallparams($formatedParams);
	}

  public function onSoapCall(EaiEvent $event) {
    $connector = $event->getObj();
    $result = $connector->getResults();
    $connector->setResults($result->GetCustomerOrdersChangedResult->CustomerOrderEntity);
    //dump($connector->getResults());
//    $result = $result->GetStockImageResult->StockStateEntity;
//    foreach($result as $r) {
//      $connector->setResults(array($r));
//      return;
//    }
    unset($result);
  }

  //public function onStart_EaiConnectorMage_eaiWriteRawData(EaiEvent $event)
  public function onEaiWriteRawDataOutStart(EaiEvent $event)
  {
    $connector = $event->getObj();
    $connector->setRawData(array($connector->getRawData()));
  }
 }