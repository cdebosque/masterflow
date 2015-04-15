<?php require_once('app/Mage.php'); Mage::app();


$handle = fopen("CDM-clients.csv", 'r');
//$rows = fgetcsv($handle, 69, ',', '"');
//var_dump($rows);exit;
//foreach(fgetcsv($handle, 69, ',', '"') as $index =>  $row) {
$report = array('nbUpdates'    => 0
              , 'nbSameCart'   => 0
              , 'nbNoResult'   => 0
              , 'nbNoEmail'    => 0
              , 'nbNoCustomer' => 0
              , 'nbOtherCart'  => 0
              , 'nbNoSaves'    => 0
);
$nbLines = 0;
while(!feof($handle)) {
    $nbLines++;
    //if($nbLines < 170146) continue;
    $row = fgetcsv($handle);
    $cartfid = $row[3];

    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    $writeConnection = $resource->getConnection('core_write');

    $selectQuery = "SELECT email FROM dpam_cdm_customers WHERE cdm_card_number = '$cartfid'    ";
    $results = $readConnection->fetchAll($selectQuery);//TODO marche pas meme quand il y a des donénes
    if (count($results)) {
        $email = $results[0]['email'];
        if ($email) {

            $customer = Mage::getModel("customer/customer")->setWebsiteId(1)->loadByEmail($email);
            if (is_object($customer) && count($customer->getData())) {
                if ($customer->getData('cdm_customer_id') ){
                    $customer->setData('cdm_customer_id', $cartfid);
                    $customer->save();
                    if ($customer->getData('cdm_customer_id')){
                        $report['nbUpdates']++;
                    } else {
                        $report['nbNoSave']++;
                        echo "<br/>Error saving cartfid $cartfid for customer $email";
                    }
                } else {
                    if ($customer->getData('cdm_customer_id') == $cartfid) {
                        $report['nbSameCart']++;
                        echo "<br/>info customer '$email' have already an cartfid ".$customer->getData('cdm_customer_id')." instead of $cartfid";
                    } else {
                        $report['nbOtherCart']++;
                        //echo "<br/>Warning customer '$email' have already the same cartfid $cartfid";
                    }
                }
            } else {
                $report['nbNoCustomer']++;
                echo "<br/>Error customer not found with email $email";
            }

            /*
            echo "<br/>$updateQuery";
            $r = $writeConnection->query();
            if(!$r) {
                echo "<hr/>Erreur, break";
                break;
            }
            */
        } else {
            $report['nbNoEmail']++;
            echo "<br/>Error No email for cdm_card_number $cartfid "; var_dump($email);
        }
    } else {
        $report['nbNoResult']++;
        echo "<br/>Error Result empty for cdm_card_number $cartfid";
    }


    //if ($nbLines > 8) break;//TODO pour le dev
}//while

$report['nbLines'] = $nbLines;

echo "<hr/>Report:<pre>";
print_r($report);
echo "</pre>";