<?php

class Observer {
  public function beforeSend(EaiEvent $event)
  {
    $connector = $event->getObj();
    $order = $connector->getRawData();
    $order['numero_ls'] = (int)$order['numero_ls'];
    $connector->setRawData(array($order));
  }
}
