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
      $params['filters']['order']['status'] = array('in' => array('payment_verified','payment_validated'));
      $params['filters']['order']['is_virtual'] = 0;
      $params['filters']['product']['product_type'] = array('neq' => 'configurable');
      $params['filters']['product']['sku'] = array('nlike' => '%CCADEAUVIRT%');
    $params['filters']['product']['is_virtual'] = array(array('null'=>'null'),array('eq'=>'0'));
      $params['filters']['order']['entity_id'] = array("gt" => $params['filters']['entity_id']);

	  $connector->setMethodparams($params);

	  $params = $connector->getMethodparams();
	}

  public function onFetchRawDataInFinish(EaiEvent $event)
	{
	    $element= $event->getObj()->getRawdata();

	    $newElement= array();

      self::$incrementId = $rawData['increment_id'] = $element['header']['numero_commande'];

      $element['header']['numero_commande']
        = $element['header']['numero_ls']
        = str_pad($element['header']['numero_commande'], 10, '0', STR_PAD_LEFT);
	    $newElement[] = $element['header'];
      foreach($element['lines'] as $l) {
        $l['quantite'] = (int)$l['quantite'];
        $l['numero_ls'] = str_pad($element['header']['numero_ls'], 10, '0', STR_PAD_LEFT);
        $newElement[]= $l;
      }

	  if (self::$incrementId)  $event->getObj()->setRawdata($newElement);

      /* mise à jour du status */
      if (self::$incrementId) {
        $data = array(
            'increment_id' => self::$incrementId,
            'state' => 'processing',
            'status' => 'logistic_validation',
            'comment' => 'Ordre de préparation vers WES'
            );
        self::$connectorIn->getObj()->update('ESB', $data);
      }
	}
}
