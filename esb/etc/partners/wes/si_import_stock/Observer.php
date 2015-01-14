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
        /* @var $connector EaiConnectorMage */
        $connector = $event->getObj();

        $mageObj = $connector->getObj();
        
        $mageObj->calculate('SI');
     }
  }
}
