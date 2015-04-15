<?php
class Observer extends EaiObserverProduct
{

	public function beforeSend(EaiEvent $event)
	{
	    $rawData= $event->getObj()->getRawData();
      //dumpy($rawData);

      $rawData['ext_order_id'] = 'MIGR'.$rawData['increment_id'];
      
      foreach ($rawData['items'] as $id => $v) {
        $newItem = array();
        $newItem['ocp_ean'] = $v['products_barcode'];//'0000005786077';
        $newItem['qty_ordered'] = $v['products_quantity'];
        $newItem['cost'] = $v['products_price'];
        $newItem['formatPrice'] = $v['final_price'];
        $newItem['original_price'] = $v['products_price'];
        $newItem['tax_percent'] = $v['products_tax'];
        $newItem['tax_amount'] = $v['products_tax_value'];
        $rawData['items'][$id] = $newItem;
      }

      switch($rawData['orders_status']) {
        case  '0' : $rawData['state'] = 'pending_payment';
                    $rawData['status'] = 'pending_payment';
                    break;

        case '30' : $rawData['state'] = 'complete';
                    $rawData['status'] = 'delivered';
                    break;

        default : $rawData['state'] = 'complete';
                  $rawData['status'] = 'delivered';
                  break;

      }

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

      $rawData['paymentMethod'] = 'checkmo';

      $rawData['discount_amount'] = $rawData['ot_coupon'] + $rawData['ot_credit'] + $rawData['ot_giftcard'];

	    $event->getObj()->setRawData(array($rawData));
	}


}