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

      $params['filters']['status'] = array("in" => array(
        'canceled',
        'closed'
      ));
      $params['filters']['ocp_crosslog_canceled_at'] = array("null" => true);

      $connector->setMethodparams($params);

      $params = $connector->getMethodparams();
    }

  public function beforeSend(EaiEvent $event) {
    $rawData = $event->getObj()->getRawData();
    $result['p_CustomerOrderNumber'] = self::$incrementId = $rawData['increment_id'];
    unset($rawData);
    $event->getObj()->setRawData(array(array($result)));
  }

  public function onWriteRawDatasOutFinish(EaiEvent $event) {
      $connector = $event->getObj();
      $client = $connector->getClient();
      $result = $client->__getLastResponse();

        $sql = "
            UPDATE sales_flat_order
            SET ocp_crosslog_canceled_at = '".date("c")."'
            WHERE increment_id = '".self::$incrementId."'
			";
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$statement = $connection->query($sql);

  }


}
