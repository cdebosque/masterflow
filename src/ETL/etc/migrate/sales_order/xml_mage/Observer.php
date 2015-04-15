<?php
class Observer extends EaiObserverProduct
{

	public function beforeSend(EaiEvent $event)
	{
      Mage::register('ocp_hack_migration', 1, true);


	    $rawData= $event->getObj()->getRawData();

      //dump($rawData);

      $rawData['ext_order_id'] = 'MIGR'.$rawData['increment_id'];

      //$rawData['increment_id'] += 90000000;

      $rawData['shipping_amount'] = $rawData['shipping_amount'] + $rawData['ot_agr_freight'];

      $rawData['discount_amount'] = - $rawData['ot_coupon'] - $rawData['ot_credit']
          - $rawData['ot_giftcard'];

      if (isset($rawData['items']['products_barcode'])) {
        // Si on a qu'un produit dans la commandes on doit créer mettre ce produit dans un tableau
        $rawData['items'] = array($rawData['items']);
      }

      foreach ($rawData['items'] as $id => $v) {
        
        $newItem = array();
        $newItem['ocp_ean'] = $v['products_barcode'];
        $newItem['qty_ordered'] = $v['products_quantity'];

        if ($rawData['discount_amount']) {
          $newItem['discount_amount'] = (float)$rawData['discount_amount'] * ((float)$v['final_price'] / (float)$rawData['ot_subtotal']);
          //dump($newItem['discount_amount'], $rawData['discount_amount'], $v['final_price'], $rawData['ot_subtotal']);
          

        } else {
          $newItem['discount_amount'] = 0;
        }
        
        $v['final_price'] = $v['final_price'] / $newItem['qty_ordered'];

        $newItem['cost'] = round((float)$v['products_price'] / (100 + $v['products_tax']) * 100, 5);
        $newItem['price'] = round((float)$v['final_price'] / (100 + $v['products_tax']) * 100, 5);
        
        $newItem['original_price'] = $v['final_price'];
        $newItem['discount_percent'] = 0;//$newItem['discount_amount'] / $newItem['qty_ordered'] / $v['final_price'] * 100;
        //dump($newItem['discount_percent'],$newItem['discount_amount'],$newItem['qty_ordered'],$v['final_price'] * 100);
        
        $newItem['tax_percent'] = $v['products_tax'];
        $newItem['tax_amount'] = $v['products_tax_value'];
        $newItem['name'] = $v['products_name'].' '.$v['products_model'];

        $rawData['items'][$id] = $newItem;
      }

      switch($rawData['status']) {
        case  '0' :
        case  '6' :
        case  '2' : $rawData['state'] = 'error';
                    $rawData['status'] = 'error';
                    break;                  
        case  '51': 
        case  '3' : $rawData['state'] = 'canceled';
                    $rawData['status'] = 'canceled';
                    break;
        case  '4' : $rawData['state'] = 'new';
                    $rawData['status'] = 'fraud';
                    break;
        case  '5' : $rawData['state'] = 'new';
                    $rawData['status'] = 'proof_requested';
                    break;
        case  '53' :
        case  '54' :
        case  '55' :
        case  '56' :
        case  '57' :
        case  '58' :
        case  '59' :
        case  '60' :
        case  '61' :
        case  '64' :
        case  '65' :
        case  '9' :
        case  '8' :
        case  '11' :
        case  '12' :
        case  '13' :
        case  '7' : $rawData['state'] = 'a reprendre';
                    $rawData['status'] = 'proof_requested';
                    break;
        case  '1' : $rawData['state'] = 'pending_payment';
                    $rawData['status'] = 'pending_payment';
                    break;

        case '15' :
        case '16' :
        case '30' : $rawData['state'] = 'complete';
                    $rawData['status'] = 'delivered';
                    break;

        default : $rawData['state'] = 'complete';
                  $rawData['status'] = 'delivered';
                  break;

      }

      // gestion des commandes étrangeres ?
      $rawData['tax_percent'] = $rawData['ot_tax'] == '19.6' ? 19.6 : 0;

//      $rawData['billing_street'] = $rawData['customers_street_address'];
//      $rawData['billing_city'] = $rawData['customers_city'];
//      $rawData['billing_country_id'] = $rawData['customers_country_id'];
//      $rawData['billing_postcode'] = $rawData['customers_postcode'];
//      $rawData['billing_phone'] = $rawData['customers_telephone'];
//
//      $rawData['shipping_street'] = $rawData['delivery_street_address'];
//      $rawData['shipping_city'] = $rawData['delivery_city'];
//      $rawData['shipping_country_id'] = $rawData['delivery_country_id'];
//      $rawData['shipping_postcode'] = $rawData['delivery_postcode'];
//      $rawData['shipping_phone'] = $rawData['delivery_telephone'];
      
//      $rawData['increment_id'] = $rawData['orders_id'];
//      $rawData['parent_id'] = $rawData['orders_parent'];
//      $rawData['created_at'] = $rawData['date_purchased'];
//      $rawData['updated_at'] = $rawData['last_modified'];
//      $rawData['tax_amount'] = $rawData['ot_tax'];

      //$rawData['payment_method'] = $rawData['paymentMethod'];
      $rawData['paymentMethod'] = 'checkmo';
      
	    $event->getObj()->setRawData(array($rawData));
	}


}
