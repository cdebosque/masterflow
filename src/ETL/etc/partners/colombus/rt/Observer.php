<?php
class Observer extends EaiObserverStandard
{
	protected static $header;
	protected static $noline = 0;


    public function onFetchRawDataInStart(EaiEvent $event)
    {
        $connector = $event->getObj();

        $params = $connector->getMethodparams();

        $params['filters']['po_status'] = 'complete';
        $params['filters']['po_delivery_percent'] = array('gt' => 0);
        $params['filters']['(ocp_esb_flag & 1)'] = 0;

        $params['store'] = null;
        $params['withProducts'] = true;
        $params['limit'] = 1;

        $params['updateFlag'] = true;

        $connector->setMethodparams($params);
    }

    public function onFetchElementsInFinish(EaiEvent $event)
    {
        //dump("event : onFetchElementsInFinish");
        $element = $event->getObj()->getElements();

        if (isset($element[0]) ) {
            $element = $element[0];
            $newElement= array();
            if (isset($element['products'])) {
                $products = $element['products'];
                //dump($products);
                unset($element['products']);
            } else {
                $products = array();
                $newElement = $element;
            }
            foreach ($products as $product) {
                $newElement[]= array_merge($element, $product);
            }
        } else {
            $newElement= false;
        }
        //dump('$newElement', $newElement);
        $event->getObj()->setElements($newElement);
    }


    function onMapOut(EaiEvent $event) //beforeSend(EaiEvent $event)
    {
        $obj     = $event->getObj();
        $porder   = $obj->getEaiData();
        //dump($porder);
        $lines   = array();
        if (is_null(static::$header)) {
            static::$header = "<ENTETE>EXP  000.000" .date('d/m/YH:i:s'). "IINT FINT          ";//TODO savoir ce que veux dire chaque constante
            $lines[] = array(static::$header);
        }

        if (isset($porder['ocp_num_line']) && $porder['ocp_num_line']) {
            static::$noline = $porder['ocp_num_line'];
        } else {
            static::$noline++;
        }

        //Si on a une commande fournisseur
        if (!empty($porder)) {

            $code = (empty($porder['po_order_id'])) ? '' : explode('-',$porder['po_order_id']);

            //Champs 1 : Code souche
            $code_souche = 'RINT';

            //Champs 2 : Numero de Bon
            $numero_bon= (empty($code[1])) ? '' : $code[1];

            //Champs 3 : Numéro de ligne
            $numero_ligne = static::$noline;

            //Champs 4 : Code souche du bon de transfert
            $code_souche_transfert =  (empty($code[0])) ? '' : $code[0];

            //Champs 5 : Numéro de Bon de transfert
            $numero_bon_transfert = (empty($code[1])) ? '' :$code[1];

            //Champs 6 : numéro de ligne de tansfert. Gérer un champs en db pour les trous
            $numero_ligne_transfert= static::$noline;//(empty(static::$noline)) ? '1' : static::$noline;

            //Champs 7 : Catégorie de document
            $categorie_document = 'CEN';

            //Champs 8 : Libellé du bon
            $libelle_bon = (empty($code[1])) ? '' : 'RINT-' . $code[1];

            //Champs 9 : Date de saisie
            $date_saisie = (empty($porder['po_date'])) ? '' : date_format(date_create($porder['po_date']),'d/m/y');

            //Champs 10 : Code société du bon
            $code_societe = '1';

            //Champs 11 : Code magasin d'origine du transfert
            $code_magasin_origine = 'DEP';

            //Champs 12 : Code magasin destinataire du transfert
            $code_magasin_destinataire = 'DTINT';

            //Code sku
            $sku = (empty($porder['sku'])) ? '' : $porder['sku'];

            //Code-à-barre
            $ean = (empty($porder['ocp_ean'])) ? '' : $porder['ocp_ean'];

            //Quantité transférée
            $quantites = (is_null($porder['pop_supplied_qty'])) ? '0' : $porder['pop_supplied_qty'];

            //Prix unitaire ligne
            $pu_ligne = '';

            //Numéro de Colis
            $numero_colis = '';

            //Code du Colis Standard
            $code_colis_standard = '';

            //Code à barre du Colis Standard
            $code_barre_colis = '';


            //On créé la ligne
            $lines[] = array(  1 => $code_souche                 //Code souche
                            ,  2 => $numero_bon					//Numero de Bon
                            ,  3 => $numero_ligne				//Numero de ligne
                            ,  4 => $code_souche_transfert       //Code souche du bon de transfert
                            ,  5 => $numero_bon_transfert        //nuémro de bon de transfert
                            ,  6 => $numero_ligne_transfert      //numéro de ligne du bon de transfert
                            ,  7 => $categorie_document 			//Bon avec réception
                            ,  8 => $libelle_bon 				//Libellé du bon
                            ,  9 => $date_saisie 				//Date de saisie
                            , 10 => $code_societe 				//Code société du bon
                            , 11 => $code_magasin_origine 		//Code magasin d'origine du transfert
                            , 12 => $code_magasin_destinataire 	//Code magasin destinataire du transfert
                            , 13 => $sku 						//Code sku
                            , 14 => $ean                         //Code-à-barre
                            , 15 => $quantites                   //Quantité transférée
                            , 16 => $pu_ligne                    //Prix unitaire ligne
                            , 17 => $numero_colis                //Numéro de Colis
                            , 18 => $code_colis_standard         //Code du Colis Standard
                            , 19 => $code_barre_colis.' '            //Code à barre du Colis Standard
            )               ;


        }
        dump($lines);
        $obj->setElement($lines);//mutiples
    }

}