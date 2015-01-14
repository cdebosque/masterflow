<?php


class Observer extends EaiObserverProduct
{
	protected static $header;
	protected static $noline = null;
	protected static $sent = false;
	protected static $po;
	protected static $po_ligne;
	protected static $po_number;
	protected static $supplier_id = 1;


	public function beforeSend(EaiEvent $event)
    {
		if(static::$sent == false)
		{
			$rawData= $event->getObj()->getRawData();
			//var_dump("D�but beforeSend");
			$rawData = static::$po;
			$rawData['products'] =static::$po_ligne;

			//var_dump($rawData);
			if($event->getObj()->getClass()  == "EaiConnectorSoap"){
			    $custEaiData= array(array(static::$supplier_id, $rawData));
            } else {
                $custEaiData= array(static::$supplier_id, $rawData);
            }

			//var_dump($custEaiData);
			static::$sent = true;
			// c'est le tableau que tu va passer a la methode de l'api
		}
		else
		{
			$custEaiData = array(array(NULL,array()));
			$custEaiData = false;
		}

		$event->getObj()->setRawData($custEaiData);
	}

	function onMapOut(EaiEvent $event) //beforeSend(EaiEvent $event)
	{
        $dev = false;//mode developpeur ou pas (code cmd et quté random)

		$obj     = $event->getObj();
		$datas   = $obj->getEaiData();
		$lines = array();

		if (!is_null(static::$noline))
		{
			//On v�rifie le num�ro de commande
            $datas['sku'] = trim($datas['sku']);
			$po_number = (empty($datas['po_order_id'])) ? '' : $datas['po_order_id'];
			$lines = array(	'sku' => (empty($datas['sku'])) ? '' : $datas['sku'],
							//'ean' =>(empty($datas['ean'])) ? '' : $datas['ean'],
							'ocp_ean' =>(empty($datas['ocp_ean'])) ? '' : $datas['ocp_ean'],
							'pop_qty' =>(empty($datas['pop_qty'])) ? '' : $datas['pop_qty'],//.($dev ? rand(-5,5) : ''),
                            'ocp_num_line' => $datas['cust_numero_ligne']
						);
			if (is_null(static::$po_number))
			{
				//On converti les dates
				$date_po = (empty($datas['po_date'])) ? '' :  ConvertDate($datas['po_date']);
				$date_supply = (empty($datas['po_supply_date'])) ? '' :  ConvertDate($datas['po_supply_date']);
				//On enregistre la commande et on cr�� l'ent�te
				static::$po_number = (empty($datas['cust_code_souche'])) ? '-' : $datas['cust_code_souche'] . '-';
				static::$po_number .= (empty($datas['po_order_id'])) ? '' : $datas['po_order_id'];
				static::$po = array(	'po_order_id' => ($dev ? "xxxx-".rand(100,999).'-' : '').static::$po_number,
										'po_date' => $date_po,
										'po_currency' => (empty($datas['po_currency'])) ? '' : $datas['po_currency'],
										'po_currency_changer_rate' => (empty($datas['po_currency_changer_rate'])) ? '' : $datas['po_currency_changer_rate'],
										'po_supply_date' => $date_supply,
										'po_carrier' => 'Non utilise',
										'po_payment_type' => 'Cheque',
										'po_taxte_rate' => '19.60',
										'po_status' => 'new',
										'po_delivery_percent' => '0',
										'po_missing_price' => '1',
										'po_purchase_nature' =>'world',
										'po_target_warehouse' => 1

									);
			}
			
			//On cr�� la 
			static::$po_ligne[static::$noline] = $lines;
			static::$noline++; 
		}
		else
		{
            static::$noline = 0; 
			
		}

		$obj->setElement(static::$po_ligne);//mutiples
	}

	
}
	
	function ConvertDate($datetoconvert)
	{
		$date_tmp =  explode('/',substr($datetoconvert,0,10));
		
		$converteddate = $date_tmp[2] . '-' . $date_tmp[1] . '-' . $date_tmp[0];
		//var_dump($date_tmp);
		
		return $converteddate;
	}