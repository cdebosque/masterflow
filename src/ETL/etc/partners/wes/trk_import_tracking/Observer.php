<?php

class Observer {
  public function beforeSend(EaiEvent $event)
  {
    $connector = $event->getObj();
    $order = $connector->getRawData();
    $order['id_commande'] = ltrim($order['id_commande'],'0');
    sleep(3);
    $connector->setRawData(array($order));
  }
}
