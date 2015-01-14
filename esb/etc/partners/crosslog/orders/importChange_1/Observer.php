<?php

class Observer Extends EaiObserverCrosslog {

	//public function onStart_EaiConnectorSoap_eaiFetchRawData(EaiEvent $event)
    public function onEaiFetchRawDataInStart(EaiEvent $event)
	{
      $connector = $event->getObj();

      $params = $connector->getCallparams();

      //$params2->p_CustomerOrderNumber = 100000004;
      $params2['p_CustomerOrderNumber'] = 100002737;

      $formatedParams = array($params['action'], array($params2));


      $connector->setCallparams($formatedParams);
	}

  public function onSoapCall(EaiEvent $event) {
    $connector = $event->getObj();
    $result = $connector->getResults();

    //dump($result->GetCustomerOrdersChangedResult->CustomerOrderEntity);
    $connector->setResults(array($result->GetCustomerOrderResult));
    unset($result);
  }

  //public function onStart_EaiConnectorMage_eaiWriteRawData(EaiEvent $event)
  public function beforeSend(EaiEvent $event)
  {
    $connector = $event->getObj();
    $order = self::convertCrosslogOrder($connector->getRawData());
    $connector->setRawData(array($order));
  }
  
 }
