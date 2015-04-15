<?php
class Observer extends EaiObserverStandard
{
	protected static $header;
	protected static $noline = 0;
	
	/*public function beforeSend(EaiEvent $event) {
        $rawData = $event->getObj()->getRawData();
	}*/

    public function onFetchRawDataInStart(EaiEvent $event)
    {
        $connector = $event->getObj();

        $params = $connector->getMethodparams();

        $params['filters']['po_status'] = 'complete';
        $params['filters']['po_delivery_percent'] = array('gt' => 0);
        $params['filters']['(ocp_esb_flag & 2)'] = 0;

        $params['store'] = null;
        $params['withProducts'] = true;
        $params['limit'] = 1;

        $params['updateFlag'] = true;

        $connector->setMethodparams($params);
    }

    /**
     * Attention un seul element en entrée, pas de boucle. mettre des limit a 1
     *
     * @param EaiEvent $event
     */
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
        $porder  = $obj->getEaiData();
        $lines   = array();

        if (is_null(static::$header)) {
            static::$header = "<ENTETE>EXP  006.000" .date('d/m/YH:i:s'). "IINT FINT          ";//TODO savoir ce que veux dire chaque constante
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
            $code_souche = 'BTINT';

            //Champs 2 : Numero de Bon
            $numero_bon= (empty($code[1])) ? '' : $code[1];

            //Champs 3 : Numéro de ligne
            $numero_ligne= static::$noline;

            //Champs 4 : Catégorie de document
            $categorie_document = 'REA';

            //Champs 5 : Validation
            $validation = 'True';

            //Champs 6 : Solder
            $solder = 'False';

            //Champs 7 : Bon avec réception
            $bon_reception = 'False';

            //Champs 8 : Libellé du bon
            $libelle_bon = (empty($code[1])) ? 'BTINT' : 'BTINT-' . $code[1];

            //Champs 9 : Date de saisie
            $date_saisie = (empty($porder['po_date'])) ? '' : date_format(date_create($porder['po_date']),'d/m/y');

            //Champs 10 : Code devise
            $code_devise = (empty($porder['po_currency'])) ? '' : $porder['po_currency'];

            //Champs 11 : Taux devise
            $taux_devise = (empty($porder['po_currency_change_rate'])) ? '' : floor($porder['po_currency_change_rate']);

            //Champs 12 : Date de début de transfert prévue
            $date_debut = (empty($porder['po_supply_date'])) ? '' : date_format(date_create($porder['po_supply_date']),'d/m/y');

            //Champs 13 : Date de fin de transfert prévue
            $date_fin = (empty($porder['po_supply_date'])) ? '' : date_format(date_create($porder['po_supply_date']),'d/m/y');

            //Champs 14 : Code société du bon
            $code_societe = '1';

            //Champs 15 : Code magasin d'origine du transfert
            $code_magasin_origine = 'DTINT';

            //Champs 16 : Code magasin destinataire du transfert
            $code_magasin_destinataire = '0002';

            //Champs 17 : Code tarif
            $code_tarif = '';

            //Champs 18 : Code événement promotionnel
            $code_event_promo = '';

            //Champs 19 : Code transporteur
            $code_transport = '';

            //Champs 20 : Mode de transport
            $mode_transport = '';

            //Champs 21 : Conditionnement
            $conditionnement = '';

            //Champs 22 : Condition de livraison
            $condition_livraison = '';

            //Champs 23 : Bon de retour
            $bon_retour = 'False';

            //Champs 24 : Motif de retour
            $motif_retour = '';

            //Code sku
            $sku = (empty($porder['sku'])) ? '' : $porder['sku'];

            //Code-à-barre
            $ean = (empty($porder['ocp_ean'])) ? '' : $porder['ocp_ean'];

            //Quantité transférée
            $quantites = (is_null($porder['pop_supplied_qty'])) ? '' : $porder['pop_supplied_qty'];

            //Prix unitaire ligne
            $pu_ligne = '';

            //Numéro de Colis
            $numero_colis = '';

            //Code du Colis Standard
            $code_colis_standard = '';

            //Code à barre du Colis Standard
            $code_barre_colis = '';

            //Libellé Sku
            $libelle_sku = '';

            //Souche de la demande de transfert
            $souche_demande = '';

            //Numéro de la demande de transfert
            $numero_demande = '';

            //Numéro de la ligne de la demande de transfert
            $numero_ligne_demande = '';

            //Numéro de souche de commande
            $numero_souche_commande = '';

            //Numéro de commande
            $numero_commande = '';

            //Numéro de ligne
            $numero_ligne_commande = '';

            //Code client détail
            $code_client_detail = '';

            //Nom du client
            $nom_client = '';

            //Prénom du client
            $prenom_client = '';

            //Nom de l'adresse
            $nom_adresse = '';

            //Enseigne
            $enseigne = '';

            //Adresse1
            $adresse1 = '';

            //Adresse 2
            $adresse2 = '';

            //Localité
            $localite = '';

            //Code postal
            $code_postal = '';

            //Pays
            $pays = '';

            //Ville
            $ville = '';

            //Reliquat
            $reliquat = '';

            //Souche du BT validé
            $souche_bt_valide = '';

            //Numéro du BT validé
            $no_bt_valide = '';

            //On créé la ligne
            $lines[] = array(  	1 => $code_souche,     				//Code souche,
                2 => $numero_bon,					//Numero de Bon
                3 => $numero_ligne,					//Numero de ligne
                4 => $categorie_document, 			//Catégorie de document
                5 => $validation, 					//Validation
                6 => $solder, 						//Solder
                7 => $bon_reception, 				//Bon avec réception
                8 => $libelle_bon, 					//Libellé du bon
                9 => $date_saisie, 					//Date de saisie
                10 => $code_devise, 				//Code devise
                11 => $taux_devise, 				//Taux devise
                12 => $date_debut, 					//Date de début de transfert prévue
                13 => $date_fin, 					//Date de fin de transfert prévue
                14 => $code_societe, 				//Code société du bon
                15 => $code_magasin_origine, 		//Code magasin d'origine du transfert
                16 => $code_magasin_destinataire, 	//Code magasin destinataire du transfert
                17 => $code_tarif, 					//Code tarif
                18 => $code_event_promo, 			//Code événement promotionnel
                19 => $code_transport, 				//Code transporteur
                20 => $mode_transport, 				//Mode de transport
                21 => $conditionnement, 			//Conditionnement
                22 => $condition_livraison, 		//Condition de livraison
                23 => $bon_retour, 					//Bon de retour
                24 => $motif_retour, 				//Motif de retour
                25 => $sku, 						//Code sku
                26 => $ean, //Code-à-barre
                27 => $quantites, //Quantité transférée
                28 => $pu_ligne, //Prix unitaire ligne
                29 => $numero_colis, //Numéro de Colis
                30 => $code_colis_standard, //Code du Colis Standard
                31 => $code_barre_colis, //Code à barre du Colis Standard
                32 => $libelle_sku, //Libellé Sku
                33 => $souche_demande, //Souche de la demande de transfert
                34 => $numero_demande, //Numéro de la demande de transfert
                35 => $numero_ligne_demande, //Numéro de la ligne de la demande de transfert
                36 => $numero_souche_commande,//Numéro de souche de commande
                37 => $numero_commande, //Numéro de commande
                38 => $numero_ligne_commande, //Numéro de ligne
                39 => $code_client_detail, //Code client détail
                40 => $nom_client, //Nom du client
                41 => $prenom_client, //Prénom du client
                42 => $nom_adresse, //Nom de l'adresse
                43 => $enseigne,//Enseigne
                44 => $adresse1, //Adresse1
                45 => $adresse2, //Adresse 2
                46 => $localite,//Localité
                47 => $code_postal, //Code postal
                48 => $pays, //Pays
                49 => $ville, //Ville
                50 => $reliquat, //Reliquat
                51 => $souche_bt_valide,//Souche du BT validé
                52 => $no_bt_valide.' ' //Numéro du BT validé

            );
        }
        $obj->setElement($lines);//mutiples
    }

}