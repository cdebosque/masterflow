<?php

class Observer Extends EaiObject {

    public function beforeSend(EaiEvent $event)
    {
        $dev = false;

        if (1) {

            $rawData= $event->getObj()->getRawData();
            // $rawdata c'est ton tableau appres mapping
            if($dev) {
                $rawData['qty'] = 10;//rand(-2,6);
            }
            if(isset($rawData['identified_by']) && isset($rawData[$rawData['identified_by']])) {
                $id = $rawData[$rawData['identified_by']];
                //unset($rawData[$rawData['identified_by']]);
            } else {
                $id = $rawData['sku'];
            }
            $custEaiData = array($id, $rawData, $event->getObj()->isFirst, $event->getObj()->isLast);

            if ($event->getObj()->getClass() == 'EaiConnectorSoap') {
                $custEaiData = array($custEaiData);
            }
            // c'est le tableau que tu va passer a la methode de l'api
            //dump($custEaiData);
            $event->getObj()->setRawData($custEaiData);
        }
    }
}
