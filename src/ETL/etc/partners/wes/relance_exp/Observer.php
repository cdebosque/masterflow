<?php

class Observer Extends EaiObserverCrosslog {

  static $connectorIn = null;
  static $incrementId = '';

  public function onConnectInFinish(EaiEvent $event){
    self::$connectorIn = $event->getObj();
  }

  /*public function onConnectInFinish(EaiEvent $event){
    self::$connectorIn = $event->getObj();
  }*/

  public function onFetchRawDataInStart(EaiEvent $event)
	{
	  $connector = $event->getObj();

	  $params = $connector->getMethodparams();

	  //$params['filters']['created_at'] = array("gt" => date("Y-m-d", strtotime("-10 day")));
    $params['filters']['order']['status'] = array('in' => array('logistic_validated'));
    //$params['filters']['order']['base_total_invoiced'] = array("notnull" => true);
    $params['filters']['order']['is_virtual'] = 0;
    $params['filters']['product']['product_type'] = array('neq' => 'configurable');    
    $params['filters']['product']['sku'] = array('nlike' => '%CCADEAUVIRT%');
    $params['filters']['product']['is_virtual'] = 0;
    unset($params['filters']['order']['status']);
    $params['filters']['order']['increment_id'] = array("in" => array(100078889));
	  $connector->setMethodparams($params);

	  $params = $connector->getMethodparams();
	}


  public function onFetchRawDataInFinish(EaiEvent $event)
	{
      $element= $event->getObj()->getRawdata();

//dump($element);exit();

	    $newElement= array();
        $element['header']['poids_colis'] = round($element['header']['poids_colis'],2);
        $element['header']['valeur_colis'] = round($element['header']['valeur_colis'],2);
	    $newElement[]= $element['header'];
		self::$incrementId = $element['header']['id_commande'];
		
	    if (self::$incrementId) $event->getObj()->setRawdata($newElement);

      

      if (self::$incrementId) {
        // Génération des documents d'expédition
        $generator = Mage::getModel('wes/sales_order_pdf_api');
        $generator->generate(
          DIR_WORKBASE.Esb::registry('identifier').'/'.Esb::counter('EXP',6),
          array('increment_id' => self::$incrementId));

        // Mise à jour du status de commande
        $data = array(
            'increment_id' => self::$incrementId,
            'state' => 'complete',
            'status' => 'logistic_packaged'
            );
        self::$connectorIn->getObj()->update('ESB', $data);
      }

	}
}
