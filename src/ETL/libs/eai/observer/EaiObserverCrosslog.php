<?php

class EaiObserverCrosslog extends EaiObserverCore {

  static $username;
  static $password;

  static $mappingArray = array(
    10 => array("status" => "logistic_incomplete_order", "state" => "processing", "label" => "Commande incomplète"),
    116 => array("status" => "logistic_validated", "state" => "processing", "label" => "Commande complète"),
    101 => array("status" => "logistic_prepared", "state" => "processing", "label" => "Commande préparée"),
    102 => array("status" => "logistic_packaged", "state" => "complete", "label" => "Colis constitué"),
    12 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Départ marchandise"),
    123 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Mail envoyé"),
    121 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Annonce HUB"),
    145 => array("status" => "logistic_shipped", "state" => "complete", "label" => "En acheminement vers plate-forme de livraison"),
    122 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Info livraison transmises"),
    124 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Prise en charge transporteur"),
    13 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Arrivée partielle en PTF de tri"),
    14 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Arrivée en PTF de tri"),
    141 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Arrivée partielle en PTF de liv."),
    15 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Arrivée en PTF de liv."),
    16 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Commande à livrer - Mail envoyé"),
    20 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Message répondeur"),
    21 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Appel téléphone infructueux # 1"),
    17 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Appel téléphone infructueux # 2"),
    22 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Appel téléphone infructueux # 3 + courrier"),
    305 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non-livré-en attente d instruction"),
    23 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Attente autre marchandise"),
    29 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Contacté et rdv pris"),
    290 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Commande en Livraison partielle"),
    36 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livré - Adresse incomplete"),
    291 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Commande en Livraison"),
    31 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livré - Absent"),
    32 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livré - Pb adresse"),
    33 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livré - Refus client"),
    198 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Livré avec exédent"),
    98 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Livré partiel"),
    701 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Colis en retour"),
    70 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Ordre de retour"),
    72 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Retourné en PTF de Liv"),
    71 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Retourné au HUB"),
    75 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Retourné partiellement au stock"),
    73 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Retourné au stock"),
    79 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Livré expéditeur suite retour"),
    501 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Problème partiel"),
    35 => array("status" => "logistic_shipped", "state" => "complete", "label" => "En instance au point de retrait"),
    402 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Problème douane-papier manquant"),
    401 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Sortie de douane"),
    34 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livré - Pb accès"),
    103 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Colis pris en charge partiellement"),
    132 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Information intégrées par transporteur Agence)"),
    97 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Commande en livraison"),
    131 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Information intégrées par transporteur Hub)"),
    405 => array("status" => "logistic_shipped", "state" => "complete", "label" => "En instance de dédouanement"),
    404 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Douane-identification marchandise"),
    403 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Problème douane"),
    104 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Colis pris en charge"),
    400 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Entrée en douane"),
    11 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Départ marchandise partiel"),
    47 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Contacté relivraison cause trsp"),
    46 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Contacté relivraison cause client"),
    143 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Rendez-vous planifié"),
    80 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Ouverture Enquête"),
    808 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Erreur de colis - inversion client"),
    801 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Demande d'ouverture d'enquête"),
    301 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Enquête en cours"),
    807 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Produit cassé"),
    806 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Colis ouvert - produit manquant"),
    805 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livraison injustifiée"),
    804 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Livraison contestée"),
    803 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livré - retard"),
    802 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Déclaration erreur de préparation"),
    88 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Transporteur:IND"),
    90 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Dossier litige clos"),
    83 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Colis perdu"),
    81 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Relance Enquête"),
    293 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Avis de passage"),
    82 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Colis détérioré"),
    74 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Retourné: en attente décision / Réattribution"),
    84 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Prise En Charge:A"),
    841 => array("status" => "logistic_shipped", "state" => "complete", "label" => "doss incpt/relance clt"),
    85 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Prise En Charge:R"),
    89 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Marchand:IND"),
    87 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Dossier Transmis au Trsp"),
    86 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Réception Dossier Client"),
    142 => array("status" => "logistic_shipped", "state" => "complete", "label" => "En acheminement vers région"),
    95 => array("status" => "logistic_canceled", "state" => "canceled", "label" => "Annulé"),
    99 => array("status" => "delivered", "state" => "complete", "label" => "Livré"),
    66 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Enquête Crosslog"),
    991 => array("status" => "delivered", "state" => "complete", "label" => "Livré)"),
    30 => array("status" => "logistic_shipped", "state" => "complete", "label" => "Non livré - Problème")
  );

  static $regions = array();
  
static $mappingBadEan = array(
	'0746775053666' => '3609451856289',
	'0746775101152' => '3609452540736'
);

   /**
   * Return magento Order status from crosslog order preparation code
   * @param <type> $code
   * @return <type>
   */
  public function exportBadEan($ean) {
    if (isset(self::$mappingBadEan[$ean])) {
		return self::$mappingBadEan[$ean];
	} else {
		return $ean;
	}
  }

  /**
   * Return magento Order status from crosslog order preparation code
   * @param <type> $code
   * @return <type>
   */
  public function importBadEan($ean) {
	$tmp = array_flip(self::$mappingBadEan);
    if (isset($tmp[$ean])) {
		return $tmp[$ean];
	} else {
		return $ean;
	}
  }

  /**
   * Return magento Order status from crosslog order preparation code
   * @param <type> $code
   * @return <type>
   */
  public function getStatusFromPreparationCode($code) {
    return self::$mappingArray[$code]['status'];
  }

  /**
   * Return magento Order state from crosslog order preparation code
   * @param <type> $code
   * @return <type>
   */
  public function getStateFromPreparationCode($code) {
    return self::$mappingArray[$code]['state'];
  }

  /**
   * Return crosslog order preparation code's label
   * @param <type> $code
   * @return <type>
   */
  public function getLabelFromPreparationCode($code) {
    return self::$mappingArray[$code]['label'];
  }

  public function getRegionCode($id) {
    
    if (!isset(self::$regions[$id])) {
	    $region = Mage::getModel('directory/region')->load($id);
	    $regionCode = null;
	    if ($region->getId()) {
	      $regionCode = $region->getCode();
	    } else{
	      $con = new EaiConnectorDebug();
	      $con->log('region code not found (id : '.$id.')', 'warn');
	    }
	    self::$regions[$id] = $regionCode;
    }
    
    return self::$regions[$id];
  }

  /**
   * Génération du header pour l'authentification des requetes vers Crosslog
   * @param <type> $user
   * @param <type> $password
   * @return <type>
   */
  public function getXmlHeaderAuth() {
    return '<AuthHeader xmlns="http://ws.crossdesk.com">
            <Username>' . self::$username . '</Username>
            <Password>' . self::$password . '</Password>
          </AuthHeader>';
  }

  public function getOrderExistsRequest($orderId) {
    return '<SOAP-ENV:Header>'
    . self::getXmlHeaderAuth(self::$username, self::$password) . '
                  </SOAP-ENV:Header>
                  <SOAP-ENV:Body>
                    <Exists xmlns="http://ws.crossdesk.com">
                      <p_CustomerOrderNumber>' . $orderId . '</p_CustomerOrderNumber>
                    </Exists>
                  </SOAP-ENV:Body>
                ';
  }

  static function countryNeedInvoice($countryCode) {
    return !in_array($countryCode, array('DE',
                  'AT',
                  'BE',
                  'BG',
                  'CY',
                  'DK',
                  'ES',
                  'EE',
                  'FI',
                  'FR',
                  'GR',
                  'HU',
                  'IE',
                  'IT',
                  'LV',
                  'LT',
                  'LU',
                  'MT',
                  'NL',
                  'PL',
                  'PT',
                  'RO',
                  'GB',
                  'SK',
                  'SI',
                  'SE',
                  'CZ'));
  }

  static function makeCrosslogOrder($magentoOrder, $action = 'new') {
    $order['Number'] = $magentoOrder['increment_id'];
    $order['Brand'] = 'DPA';
    $order['Language'] = 'Fr';
    $order['Date'] = date('c');

    $magentoOrder['shipping_address']['country_id'] = trim($magentoOrder['shipping_address']['country_id']);

    $customer['Firstname'] = $magentoOrder['billing_firstname'];
    $customer['LastName'] = $magentoOrder['billing_lastname'];
    $customer['Number'] = max(1, $magentoOrder['customer_id']); // ???
    //$paymentInformations['Currency'] = $magentoOrder['order_currency_code'];
    $paymentInformations['InvoiceType'] = 5;
    if ($action == 'toSend') {
      $shippingGroups['Blocked'] = 0;
    } else {
      $shippingGroups['Blocked'] = 1;
    }

    if (self::countryNeedInvoice($magentoOrder['shipping_address']['country_id'])) {
        $paymentInformations['InvoiceType'] = 8;
        // TODO mettre le numero de facture plutot que celui de commande ?
        $paymentInformations['InvoiceNumber'] = $magentoOrder['increment_id'];
        $order['Language'] = 'EN';
    }

    $shippingGroups['ShippingGroupID'] = 1;

    // Gestion des transporteurs
    switch ($magentoOrder['shipping_method']) {
      case 'owebiashipping1_colis_prive_fr' :
      case 'owebiashipping1_colis_prive_freeshipping_fr' :
        $shippingGroups['ShippingMode'] = 74;
      // Pour Colis prive vers monaco il faut indiquer la france comme pays de destination
      if ($magentoOrder['shipping_address']['country_id'] == 'MC') {
        $magentoOrder['shipping_address']['country_id'] = 'FR';
      }
		break;
      case 'owebiashipping1_tnt_fr' :
      $shippingGroups['ShippingMode'] = 83;
      // Pour TNT vers monaco il faut indiquer la france comme pays de destination
      if ($magentoOrder['shipping_address']['country_id'] == 'MC') {
        $magentoOrder['shipping_address']['country_id'] = 'FR';
      }
		break;
      case 'owebiashipping1_colissimo_zone1' :
      case 'owebiashipping1_colissimo_zone1_free' :
      case 'owebiashipping1_colissimo_zone_gratuit1' :
       $shippingGroups['ShippingMode'] = $magentoOrder['shipping_address']['country_id'] == 'FR' ? 2 : 4;
        break;
      case 'owebiashipping1_colissimo_zone2' :
      case 'owebiashipping1_colissimo_zone3' :
      case 'owebiashipping1_colissimo_zone4' :
      case 'owebiashipping1_colissimo_zone5' :
      case 'owebiashipping1_colissimo_zone6' :
      case 'owebiashipping1_colissimo_zone7' :
      case 'owebiashipping1_colissimo_zone8' :
      case 'owebiashipping1_colissimo_zone9' :
      case 'owebiashipping1_colissimo_zone10' : $shippingGroups['ShippingMode'] = 4;
        break;
      case 'owebiashipping1_colissimo_zone_om1' :
      case 'owebiashipping1_colissimo_zone_om2' :
        $shippingGroups['ShippingMode'] = 5;
        break;
      case 'owebiashipping1_mrw_zone3' :
      case 'owebiashipping1_mrw_zone3_free' :
        $shippingGroups['ShippingMode'] = 92;
        break;
      case 'owebiashipping1_mondial_relay_zone1' :
      case 'owebiashipping1_mondial_relay_zone2' :
      case 'owebiashipping1_mondial_relay_zone3' :
        $shippingGroups['ShippingMode'] = 100;
        break;

      default :
        if(stripos($magentoOrder['shipping_method'], 'olissimo_encombrant_zone')) {
          $shippingGroups['ShippingMode'] = $magentoOrder['shipping_address']['country_id'] == 'FR' ? 2 : 4;
        } elseif(stripos($magentoOrder['shipping_method'], 'hronopost')) {
          $shippingGroups['ShippingMode'] = $magentoOrder['shipping_address']['country_id'] == 'FR' ? 41 : 40;
        } else {
          $shippingGroups['ShippingMode'] = 4;
        }
    }
    
    //dump($shippingGroups['ShippingMode'], $magentoOrder['shipping_description']);

    // Exception pour les DOM TOM
    if ($shippingGroups['ShippingMode'] == 4 && in_array($magentoOrder['shipping_address']['country_id'],
        array('RE', 'GP', 'GF', 'MQ', 'YT'))) {
        $shippingGroups['ShippingMode'] = 5;
    }
    if ($magentoOrder['shipping_address']['relai_id']) {
      $shippingGroups['PickUp'] = array('Number' => $magentoOrder['shipping_address']['relai_id']);
    }
    //Hack livraison vers Saint Marin
    if ($magentoOrder['billing_address']['country_id'] == 'MF') {
		$magentoOrder['billing_address']['country_id'] = 'SM';
	}

	if ($magentoOrder['shipping_address']['country_id'] == 'MF') {
		$magentoOrder['shipping_address']['country_id'] = 'SM';
	}

    //Hack livraison vers l'irlande
    if ($magentoOrder['billing_address']['country_id'] == 'IE') {
      $magentoOrder['billing_address']['postcode'] = '99999';
    }

    if ($magentoOrder['shipping_address']['country_id'] == 'IE') {
      $magentoOrder['shipping_address']['postcode'] = '99999';
    }


    //dump($shippingGroups['ShippingMode'], $magentoOrder['shipping_description'], $magentoOrder['shipping_method']);
    //exit();

    // pour les tests
    //$shippingGroups['ShippingMode'] = 999;

    $shippingAddress['Firstname'] = $magentoOrder['shipping_address']['firstname'];
    $shippingAddress['LastName'] = $magentoOrder['shipping_address']['lastname'];

    $shippingStreet = $magentoOrder['shipping_address']['street'];
    // Ajout du champ libre "État/Province" s'il est renseigné car on ne pourra pas le passer autrement.
    if (!empty($magentoOrder['shipping_address']['region'])) {
      $shippingStreet .= ' - '.$magentoOrder['shipping_address']['region'];
    }
    $shippingStreetLines = explode("\n", wordwrap($shippingStreet, 35, "\n", true));
    $shippingAddress['AddressLine1'] = $shippingStreetLines[0];
    $shippingAddress['AddressLine2'] = $shippingStreetLines[1];
    $shippingAddress['AddressLine3'] = $shippingStreetLines[2];
    //$shippingAddress['AddressLine1'] = substr($magentoOrder['shipping_address']['street'], 0, 23);
    //$shippingAddress['AddressLine2'] = strlen($magentoOrder['shipping_address']['street']) > 23 ? substr($magentoOrder['shipping_address']['street'], 23, 23) : '';
    //$shippingAddress['AddressLine3'] = strlen($magentoOrder['shipping_address']['street']) > 46 ? substr($magentoOrder['shipping_address']['street'], 46, 23) : '';

    $shippingAddress['CompanyName'] = $magentoOrder['shipping_address']['company'];
    $shippingAddress['ZipCode'] = trim($magentoOrder['shipping_address']['postcode']);
    $shippingAddress['City'] = $magentoOrder['shipping_address']['city'];
    $shippingAddress['Country'] = trim($magentoOrder['shipping_address']['country_id']);
    
    if ($magentoOrder['shipping_address']['region_id'] > 0) {
	        $shippingAddress['StateProvince'] = self::getRegionCode($magentoOrder['shipping_address']['region_id']);
    }
        
    $shippingAddress['Email'] = $magentoOrder['shipping_address']['email'];
    $shippingAddress['PhoneNumber'] = $magentoOrder['shipping_address']['telephone'];

    $shippingGroups['ShippingAddress'] = $shippingAddress;

    $billingAddress['Firstname'] = $magentoOrder['billing_address']['firstname'];
    $billingAddress['LastName'] = $magentoOrder['billing_address']['lastname'];

    $billingStreet = $magentoOrder['billing_address']['street'];
    // Ajout du champ libre "État/Province" s'il est renseigné car on ne pourra pas le passer autrement.
    if (!empty($magentoOrder['billing_address']['region'])) {
      $billingStreet .= ' - '.$magentoOrder['billing_address']['region'];
    }
    $billingStreetLines = explode("\n", wordwrap($billingStreet, 35, "\n", true));
    $billingAddress['AddressLine1'] = $billingStreetLines[0];
    $billingAddress['AddressLine2'] = $billingStreetLines[1];
    $billingAddress['AddressLine3'] = $billingStreetLines[2];
    //$billingAddress['AddressLine1'] = substr($magentoOrder['billing_address']['street'], 0, 23);
    //$billingAddress['AddressLine2'] = strlen($magentoOrder['billing_address']['street']) > 23 ? substr($magentoOrder['billing_address']['street'], 23, 23) : '';
    //$billingAddress['AddressLine3'] = strlen($magentoOrder['billing_address']['street']) > 46 ? substr($magentoOrder['billing_address']['street'], 46, 23) : '';

    $billingAddress['CompanyName'] = $magentoOrder['billing_address']['company'];
    $billingAddress['ZipCode'] = trim($magentoOrder['billing_address']['postcode']);
    $billingAddress['City'] = $magentoOrder['billing_address']['city'];
    $billingAddress['Country'] = trim($magentoOrder['billing_address']['country_id']);
    
    if ($magentoOrder['billing_address']['region_id'] > 0) {
        $billingAddress['StateProvince'] = self::getRegionCode($magentoOrder['billing_address']['region_id']);
    }

    $billingAddress['Email'] = $magentoOrder['billing_address']['email'];
    $billingAddress['PhoneNumber'] = $magentoOrder['billing_address']['telephone'];

    $paymentInformations['InvoiceAddress'] = $billingAddress;
    $paymentInformations['Reduction'] = - $magentoOrder['base_discount_amount'];

    $productLines = array();
    //dump($magentoOrder['items']); exit();
    $simples = array();
    $configurables = array();
    foreach ($magentoOrder['items'] as $item) {
      if ($item['product_type'] == 'simple' || $item['ocp_ean'] == '3609451319623') {
        $simples[] = $item;
      } elseif ($item['product_type'] == 'configurable'){
          $configurables[$item['item_id']] = $item;
      }

    }

    foreach ($simples as $item) {
      if (!empty($item['parent_item_id'])) {
          $item['base_price_incl_tax'] = $configurables[$item['parent_item_id']]['base_price_incl_tax'];
          $item['qty_canceled'] = $configurables[$item['parent_item_id']]['qty_canceled'];
          $item['qty_refunded'] = $configurables[$item['parent_item_id']]['qty_refunded'];
      }
      $productLines[] = array(
          'ProductReference' => self::exportBadEan($item['ocp_ean']),
          'OrderedQuantity' => (int)$item['qty_ordered'] - (int)$item['qty_canceled'] - (int)$item['qty_refunded'],
          'UnitPrice' => (float)$item['base_price_incl_tax'],
      );
    }

    $paymentInformations['InvoiceTotalAmount'] = $magentoOrder['grand_total'] - $magentoOrder['total_refunded'];
    $paymentInformations['ItemsTotalAmount'] = $magentoOrder['subtotal'] - $magentoOrder['subtotal_refunded'];
    $paymentInformations['OrderTotalAmount'] = $magentoOrder['grand_total'] - $magentoOrder['total_refunded'];

    $shippingGroups['ProductLines']['ProductLineEntity'] = $productLines;
    $shippingGroups['ShippingPrice'] = $magentoOrder['shipping_amount'] - $magentoOrder['shipping_refunded'];
    //$shippingGroups['ShippingTaxRate'] = $magentoOrder['base_shipping_amount'];

    $order['Customer'] = $customer;
    $order['PaymentInformations'] = $paymentInformations;
    $order['ShippingGroups']['ShippingGroupEntity'] = $shippingGroups;

    return $order;
  }

  /**
   * transform a crosslog customer order to a magento salesOrder
   * @param <type> $crosslogOrder
   */
  static function convertCrosslogOrder($crosslogOrder) {

    if (!is_array($crosslogOrder)) {
      $crosslogOrder = esb::object_to_array($crosslogOrder);
    }
    //dump($crosslogOrder);
    $order['increment_id'] = $crosslogOrder['Number'];
    $shippingGroup = $crosslogOrder['ShippingGroups']['ShippingGroupEntity'];
    /*if (is_array($shippingGroup)) {
      // TODO gere les shipping group multiple
      $shippingGroup = $shippingGroup[0];
    }*/

    $order['status'] = self::getStatusFromPreparationCode($shippingGroup['State']);
    $order['state']  = self::getStateFromPreparationCode($shippingGroup['State']);
    $order['comment']  = self::getLabelFromPreparationCode($shippingGroup['State']);

    //$order['shipping_method'] = $crosslogOrder['SupplierCode'];
    //$order['comment']         = $crosslogOrder['SupplierCode'];
    //<ShippingInfo><Comment/>

    $order['items'] = array();
    if (isset($shippingGroup['ProductLines']['ProductLineEntity']['ProductReference'])) {
      $shippingGroup['ProductLines']['ProductLineEntity']
      = array($shippingGroup['ProductLines']['ProductLineEntity']);
    }
    foreach ($shippingGroup['ProductLines']['ProductLineEntity'] as $p) {
      $order['items'][$p['ProductReference']] = array(
          'ocp_ean'     => self::importBadEan($p['ProductReference']),
          'qty_reserved' => isset($p['ReservedQuantity']) ? $p['ReservedQuantity'] : 0);
    }

    $order['shipments'] = array();
    $idShip = 0;
    if (!empty($shippingGroup['Packs'])) {
        //dump($shippingGroup['Packs']);exit();
      if (isset($shippingGroup['Packs']['PackEntity']['TrackingNumber'])) {
        $shippingGroup['Packs']['PackEntity'] = array($shippingGroup['Packs']['PackEntity']);
      }
      foreach ($shippingGroup['Packs']['PackEntity'] as $pack) {
        $order['shipments'][$idShip]['trackingNumber'] = $pack['TrackingNumber'];
        $order['shipments'][$idShip]['items'] = array();
        if (isset($pack['Lines']['PackLineEntity']['ProductReference']))
            $pack['Lines']['PackLineEntity'] = array($pack['Lines']['PackLineEntity']);
        foreach($pack['Lines']['PackLineEntity'] as $l) {
            //dump('line:',$l);
          $order['shipments'][$idShip]['items'][$l['ProductReference']] = array(
              'ocp_ean' => self::importBadEan($l['ProductReference']),
              'qty' => $l['Quantity']
          );
          $order['items'][$l['ProductReference']]['qty_shipped'] = $l['Quantity'];
        }
        $idShip++;
      }
    }
    //dump($order);exit();
    return $order;
  }

  static function makeCrosslogSupplyOrder($magentoOrder) {
    $order['Number'] = $magentoOrder['po_order_id'];
    $order['SupplierCode'] = 'DPAM';//$magentoOrder['po_sup_num'];
    $order['Date'] = date('c');
    $order['State'] = 10;
    $order['Lines']['SupplyOrderLineEntity'] = array();
    foreach ($magentoOrder['products'] as $p) {
        $product = Mage::getModel('catalog/product')->load($p['pop_product_id']);
      if ($product) {
        $ocp_ean = self::exportBadEan($product->getData('ocp_ean'));
      }
      if ($p['pop_qty'] > 0) {
        $order['Lines']['SupplyOrderLineEntity'][] = array(
            'ProductReference' => $ocp_ean,
            'Quantity' => $p['pop_qty'],
        );
      }
    }
    return $order;
  }

  /**
   * transform a crosslog supply order to a magento supplyOrder
   * @param <type> $crosslogSupplyOrder
   */
  static function convertCrosslogSupplyOrder($crosslogSupplyOrder) {

    $order['po_order_id'] = $crosslogSupplyOrder['Number'];
    $order['po_sup_num'] = $crosslogSupplyOrder['SupplierCode'];
    $order['products'] = array();
    $totalQty = 0;
    $totalReceivedQty = 0;

    $crosslogSupplyOrder['Lines']['SupplyOrderLineEntity'] = Esb::collection($crosslogSupplyOrder['Lines']['SupplyOrderLineEntity']);

    foreach ($crosslogSupplyOrder['Lines']['SupplyOrderLineEntity'] as $p) {
      $formatedP = array(
          'ocp_ean' => self::importBadEan($p['ProductReference']),
          'pop_qty' => $p['Quantity']);
      $totalQty += $p['Quantity'];
      if ($p['ReceivedQuantity']) {
        $formatedP['pop_supplied_qty'] = $p['ReceivedQuantity'];
        $totalReceivedQty += $p['ReceivedQuantity'];
      }
      $order['products'][] = $formatedP;
    }

    $order['po_delivery_percent'] = floor($totalReceivedQty / max(1, $totalQty) * 100);
    $order['po_status'] = $order['po_delivery_percent'] == 100
      ? 'complete' : 'waiting_for_delivery';
    return $order;
  }

  /**
   * gestion des erreurs crosslog
   * @param <type> $crosslogSupplyOrder
   */
  static function crosslogErrorHandler($result, $apiMethod, $orderId, $codeSucces = 0, $sendMail = false) {
    $return = array();
    $return['succes'] = false;
    $return['msg'] = $orderId.' - ';


    if (is_array($result) && isset($result['soap:Envelope']) && isset($result['soap:Envelope']['soap:Body'])
            && isset($result['soap:Envelope']['soap:Body'][$apiMethod.'Response'])) {
      // Erreur géré par crosslog
      $result = $result['soap:Envelope']['soap:Body'][$apiMethod.'Response'][$apiMethod.'Result'];
      if ($result['ErrorCode'] != 0) {
        $message = $result['Messages']['AcknowledgmentMessageEntity'];
        if (!isset($message['Message'])) {
            // dans le cas ou il y a plusieurs messages d'erreurs
            $message['Message'] = '';
            $message['Code'] = '';
            foreach ($result['Messages']['AcknowledgmentMessageEntity'] as $tmp)
                $message['Message'] .= $tmp['Code'].' '.$tmp['Message']."\n";
        }
        if ($message['Code'] == $codeSucces) {
            // message d'erreur a ignoré
            $return['succes'] = true;

        } else {
            $return['msg'] .= "Erreur chez crosslog : ".$message['Code'].' '.$message['Message'];
            if ($sendMail) mail('exploit-logistic@onlinecommercepartners.com' , 'erreur integration commande dpam '.$orderId, $return['msg']);
        }
      } else {
        // success mise a jour des status de la commande
        $return['msg'] .= " succes";
        $return['succes'] = true;

      }
    } else {
      // Erreur non géré.
      $return['msg'] .= " Erreur XML avec crosslog : ".$result;
      if ($sendMail) mail('exploit-logistic@onlinecommercepartners.com' , 'erreur integration commande dpam '.$orderId, $return['msg']);
    }
    return $return;
  }

  static function convertCrosslogSupplyOrderStatus($crosslogStatusCode) {
	$crosslogState = array();
	$crosslogState["00"] = 'new'; //En cours de creation</option>
	$crosslogState["10"] = 'waiting_for_delivery'; //En attente de réception</option>
	$crosslogState["11"] = 'complete'; //Refusée - non conforme</option>
	$crosslogState["12"] = 'waiting_for_delivery'; //Refusée partiel</option>
	$crosslogState["15"] = 'waiting_for_delivery'; //A quai</option>
	$crosslogState["20"] = 'waiting_for_delivery'; //Receptionnée partielle</option>
	$crosslogState["21"] = 'complete'; //Receptionnée - non conforme</option>
	$crosslogState["30"] = 'complete'; //Soldée</option>
	$crosslogState["99"] = 'complete'; //Annulée</option>
	return $crosslogState[$crosslogStatusCode];
  }
}
