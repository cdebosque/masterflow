<?php

class Observer {
  static $nb = 0;

  public function beforeSend(EaiEvent $event)
  {
    $connector = $event->getObj();
    $order = $connector->getRawData();
    //$order['numero_ls'] = (int)$order['numero_ls'];
    $connector->setRawData(array($order));

    if (!empty($order)) {
      self::$nb++;
    }
  }

  public function onDisconnectOutStart(EaiEvent $event)
  {
    if (self::$nb) {
      $connector = $event->getObj();
      $mageObj = $connector->getObj();

      // Recalcul du stock avec mise à jour d'ocp_flag en vue d'un reinit
      $mageObj->calculate('SI', true);

      // Remise à zero des produits avec ocp_flag à zero
      $api = new MDN_AdvancedStock_Model_CatalogInventory_Stock_Item_Api();
      $api->updateFinish(1);
    }
  }
}
