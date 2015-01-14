<?php
class Observer Extends EaiObject
{
    //ALTER TABLE `cataloginventory_stock_item` ADD `ocp_update_flag` INT NOT NULL DEFAULT '0';+rm -rf var/cache

//    public function onConnectOutFinish(EaiEvent $event)
//    {
//        /* @var $connector EaiConnectorMage */
//        $connector = $event->getObj();
//
//        if ($connector->getClass() == 'EaiConnectorSoap') {
//            $soapClient = $connector->getClient();
//            $result = $soapClient->call($connector->getSession(), 'erp_stock.updateStart');
//
//        } elseif ($connector->getClass() == 'EaiConnectorMage') {
//            /* @var $mageObj MDN_AdvancedStock_Model_CatalogInventory_Stock_Item_Api */
//            $mageObj = $connector->getObj();
//            $mageObj->updateStart();
//        }
//    }

    public function onDisconnectOutStart(EaiEvent $event)
    {
        /* @var $connector EaiConnectorMage */
        $connector = $event->getObj();


        if ($connector->getClass() == 'EaiConnectorSoap') {
            $soapClient = $connector->getClient();
            $result = $soapClient->call($connector->getSession(), 'erp_stock.updateFinish');
            //dump($result);
        } elseif ($connector->getClass() == 'EaiConnectorMage') {
            /* @var $mageObj MDN_AdvancedStock_Model_CatalogInventory_Stock_Item_Api */
            $mageObj = $connector->getObj();
            $mageObj->updateFinish();
        }
    }

    public function beforeSend(EaiEvent $event)
    {
        $dev = false;
        /** @var $connector EaiConnector */
        $connector = $event->getObj();


        if (1) {

            $rawData= $connector->getRawData();
            // $rawdata c'est ton tableau apres mapping
            if ($dev) {
                $rawData['qty'] = 10;//rand(-2,6);
            }
            if (isset($rawData['identified_by']) && isset($rawData[$rawData['identified_by']])) {
                $id = $rawData[$rawData['identified_by']];
                //unset($rawData[$rawData['identified_by']]);
            } else {
                $id = $rawData['sku'];
            }
            $custEaiData = array($id, $rawData,);

            if ($event->getObj()->getClass() == 'EaiConnectorSoap') {
                $custEaiData = array($custEaiData);
            }
            // c'est le tableau que tu va passer a la methode de l'api
            //dump($custEaiData, "row n°".$event->getObj()->getRawDatasWrited());
            $event->getObj()->setRawData($custEaiData);
        }
    }
}
