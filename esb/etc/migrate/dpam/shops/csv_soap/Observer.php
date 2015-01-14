<?php
class Observer
{

    public function beforeSend(EaiEvent $event)
    {
        $rawData= $event->getObj()->getRawData();

        $concept = array(
        'ENFANT/BEBE/CHAUSSURES' => 1, //'Enfant-Bébé-Chaussures',
        'ENFANT/CHAUSSURES' => 2, //'Enfant-Chaussures',
        'ENFANT/CHAUSSRES' => 2, //'Enfant-Chaussures',
        'CHAUSSURES' => 3, //'Chaussures',
        'ENFANT' => 4, //'Enfant',
        'OUTLET' => 5, //'Outlet',
        'BEBE/CHAUSSURES'  => 6, //'Chaussures-Bébé',
        'BEBE' => 7, //'Bébé',
        'ENFANT/BEBE' => 8, //'Enfant-Bébé'
        );

        $rawData['concept']= $concept[$rawData['concept']];
        $rawData['status']= 1;
        $rawData['is_store_delivery']= 0;

        $event->getObj()->setRawData(array(array($rawData)));
    }


    public function onSoapWrite(EaiEvent $event)
    {
        $connector= $event->getObj();

        $rawData= $connector->getRawData();
        if (!empty($rawData[0][0]['notes'])) {
            $result = call_user_func_array(array($connector->getClient(), $connector->getCall()), array($connector->getSession(), 'shops.addProperties', $rawData));
        }

    }
}