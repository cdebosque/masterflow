<?php
/**
 * Classe à surcharger permettant de factoriser le code des observers de chaque interface 'sales'
 * Les classes héritées doivent inclure cet obserber dans interface.xml - ex : <observer>../ObserverSales</observer>
 *
 * @author tbondois
 */
abstract class ObserverSales extends EaiObject
{
    /**
     * A surcharger dans chaque classe héritée pour distinguer les interfaces
     * @var string
     */
    public static $feed;

    /**
     * Chaine d'entete EXP du fichier
     * @var string
     */
    protected static $header;

    /**
     * Gestion des collection en connection soap
     * @param EaiEvent $event
     */
    public function onSoapFetch(EaiEvent $event) //beforeSend(EaiEvent $event)
	{	
		$obj     = $event->getObj();//EaiConnectorSaop
		$results = $obj->getResults();
        
        if ($obj->getProp('version') == 1) {    //version api soap du client
            $results = Esb::collection($results);
            $obj->setResults($results);
        }
        //dump('onPrepareFetching', $results);
    }

	/**
     * 
     * @param EaiEvent $event
     * @todo
     * - coupons : enlever les auters promos panier et rajouter mt frais de port
     * - envoyer UID numériques
     * - regular_unit_price_incl_tax renvoie pas le bon prix barré si les produits configurables
     * - Gérer les listes de naissance https://docs.google.com/a/oclio.com/spreadsheet/ccc?key=0AvLo_KVOl601dDl5OXpydjE2Qjk1V0prakMzTkFZNXc
     * - Tester : paypal : appliquer la com classique
     * - Tester leetchi
     * - tester TVA avec taux conversion
     * 
	 * @see Annexes https://docs.google.com/a/oclio.com/document/d/17YpdgmBx0cBuwt7Mo3eq9Gh2DV2nmFc3OKioJYaBtII/edit
     * @see Gestion des réductions https://docs.google.com/a/oclio.com/spreadsheet/ccc?key=0AvLo_KVOl601dExaSllpcFlRdlZ1NFhTaTZqc0NqV2c
     * @see Corrections 1 (ET/LT/LM/CU) https://docs.google.com/a/onlinecommercepartners.com/spreadsheet/ccc?key=0AvLo_KVOl601dHk5aEh0Z08yLXpDRzFDZ2FqUFVpbkE#gid=1
     * @see Corrections 2 (LM) https://docs.google.com/a/onlinecommercepartners.com/spreadsheet/ccc?key=0AvLo_KVOl601dFRhSkpmakNGU3plN19aRGFhNnRlOFE#gid=0
     * @see Corrections 3 (LF) https://docs.google.com/a/onlinecommercepartners.com/spreadsheet/ccc?key=0AvLo_KVOl601dE1VN3B6SlJyN0RyZHNfX2dKUTliN3c#gid=2
     * @see Corrections 4 (codes cadeaux, tva, wishlist) https://docs.google.com/a/oclio.com/spreadsheet/ccc?key=0AvLo_KVOl601dDl5OXpydjE2Qjk1V0prakMzTkFZNXc
	 */

    /**
     * Gère l'arrondi à 2 chiffres et l'inversion de signe positif/négatif pour les refunded
     *
     * @param $input
     * @return float|int|number|string
     */
    protected function formatPrice($input, $keepSign = false)
    {
        if (is_numeric($input)) {
            $r = round($input, 2);
            if(!$keepSign && static::$feed == 'refunded') {
                $r = - $r;  //caste automatiquement en numérique (int ou float)
            }
        } else {
            $r = $input;
        }
        return $r;
    }


	protected function _transco(EaiEvent $event) //beforeSend(EaiEvent $event)
	{
        /**
         * @var EaiMapper $obj
         */
        $obj     = $event->getObj();

        $feed = static::$feed;
        $keywordSolde = 'solde:';
        $keywordDefaultPromo = 'OP2';
        
        //$feed = 'ordered';//TODO paramètre d'entrée
        //dump("feed=$feed");

		$data   = $obj->getEaiData();
		dump($data);
        if(!is_array($data) || empty($data)) {
            $obj->log('no order no process', 'warn');
            return;
        }
        $mixedId = $data['order_id'].' / '.static::$feed.' '.$data['entity_id'];

        $lines   = array();

        if (1 /*or (int)$data['grand_total'] > 0*/) {
            
            dump('> traitement de la commande:', $mixedId);

            //dump($data);

            foreach (array('items', 'shipping_country_id'/*, 'shipping_address'*/) as $subkey) {
                if (!isset($data[$subkey]) || empty($data[$subkey])) {
                  $obj->log("Order n°$mixedId : missing subfield '$subkey', skipping this order. Total fields = ".count($data), 'err');
                  $obj->setElement(false);
                  return false;
                }
            }

            $souche     = 'TVI01'; //toujours TVI01 (pour TV Internet 01)
            //$numero   = $order['increment_id'];//$orderId;//numéro de ticket.
            $rootIncId  = ltrim($data['increment_id'], '0');
            $orderIncId = $data['order_increment_id'];
            //$et20 = $rootIncId;//$orderIncId
            $numero = (int)$data['entity_id'] + 1000000;

            /*if ($rootIncId >= 100000000) {  //de base l'increment id commence a 100 milion, or il faut 8 chiffres maxi
                $numero = $rootIncId - (100000000 - 1000000);
            } else {*/
            //} $numero = 1000000 + (int)$data['entity_id'];//doit pas rentrer en conflit avec un id boaboa et ils ont en sont a environ 840 000.
            if ($feed == 'refunded' ){
                $numero.= 'A';
                $rootIncId = 'A'.$rootIncId;
            }

			if (in_array($rootIncId, array('A812951', 'A813042', 'A813056'))
                or in_array($numero, array('1003339A', '1003430A', '1003492A'))
			) {	////TODO bug a resoudre "Store credit amount cannot exceed order amount." sur le save
				$obj->log("Element connu avec Store credit amount cannot exceed order amount", 'warn');
				$obj->setElement(false);
                  return false;
			}

            $societe = 1;
            $vendeur = '0002';

            if (is_null(static::$header)) {
                static::$header = "<ENTETE>EXP  008.000" .date('d/m/YH:i:s'). "IINT FINT";//TODO savoir ce que veux dire chaque constante
                $lines[] = array(static::$header . str_repeat(' ', 19));//19 ESPACES apres entete
            }

            $cartfid = $data['cdm_customer_id'];
            $uid     = $data['ocp_customer_uid'];//donnée bien récupérée. Mais c'est une chaine peut etre trop longue

            $libelle = "($orderIncId)";
            $libelle.= $cartfid ? " <$cartfid>" : '';
            $libelle.= ' {'.$rootIncId.'}';
            $libelle.= is_numeric($uid) ? " [$uid]" : '';
            $libelle.= " @INTER";



            $countryFacturation = strtoupper($data['shipping_country_id']);

            if(isset($data['coupon_code']) && !is_null($data['coupon_code'])) {
                $coupon = trim($data['coupon_code']);
            } else {
                $coupon = null;
            }

            
            $trancoRegimeFacturation = array('FR' => 'F'
                                           , 'DE' => 'D'
                                           , 'ES' => 'ES'
                                           , 'IT' => 'IT'
                                           , 'GR' => 'GR'
                                           , 'BE' => 'B'
            );

            /* les montants tva standards par pays
             voir http://edouane.com/cm/index/reglementation-douane/0/tva-vat-etats-membres.html
            TODO intégrer dans une données magento...
            */
            $trancoTauxStandardTVA = array('FR' => 19.6
                                        , 'DE' => 19
                                        , 'ES' => 21
                                        , 'IT' => 21
                                        , 'GR' => 23
                                        , 'BE' => 21
            );
            $trancoTauxLivreTVA   = array('FR' => 7
                                        , 'DE' => 7
                                        , 'ES' => 10
                                        , 'IT' => 10
                                        , 'GR' => 13
                                        , 'BE' => 12
            );

            $trancoTauxReduitTVA  = array('FR' => 5.5
                                        , 'DE' => 7
                                        , 'ES' => 10
                                        , 'IT' => 10
                                        , 'GR' => 6.5
                                        , 'BE' => 6
            );

            /*  CB - utilisé pour les règlements par carte bancaires
                AV - avoir émis (donc émis par le site comme un remboursement)
                AR - avoir reçu (utilisé par le client pour régler un achat)
                BA - Bon achat/chèque cadeaux
                LT - Leetchi
                PPC - partie commission d'un règlement paypal (0.25 Euro + 1,4% du règlement)
                PPN - partie net d'un règlement paypal (règlement - PPC)
                CAD - carte cadeaux DPAM
            */
            $paymentMethods = array('ccsave'           => "CB"
                                  , 'checkmo'          => "CB"  //todo normalement, impossible
                                  , 'free'             => "unknow_free"
                                  , 'banktransfer'     => "unknow_banktransfer"
                                  , 'cashondelivery'   => "unknow_cashondelivery"
                                  , 'paypal'           => "paypal"//cas particulier a retraiter
                                  , 'paypal_express'   => "paypal"
                                  , 'paypal_direct'    => "paypal"
                                  , 'paypal_standard'  => "paypal"
                                  , 'paypaluk_express' => "paypal"
                                  , 'paypaluk_direct'  => "paypal"
                                  , 'leetchi'          => "LT"
                                  , 'PaylineCPT'       => 'CB'
                                  , 'PaylineWALLET'    => 'AR'
            );


            
            if (round($data['base_tax_amount'], 2) == 0) {
                $codeRegime = 'EI';
                $obj->log("Pb avec $mixedId : base_tax_amount = 0, regime => EI");
            } elseif (isset($trancoRegimeFacturation[$countryFacturation])) {
                $codeRegime = $trancoRegimeFacturation[$countryFacturation];
            } else {
                //$codeRegime = 'F';//a voir pour gérer mieux, faudrait une table en plus dans magento pour faire des groupes de pays qui partagent la meme TVA
                $codeRegime = $countryFacturation; // Il faudra prévenir en cas d'ajout de nouveau pays pour que l'on ai le code
                $obj->log("Le regime de facturation pour '$countryFacturation' n'est pas implementé", 'err');
            }

            if (isset($trancoTauxStandardTVA[$countryFacturation])) {
                $tauxStandardTVA = $trancoTauxStandardTVA[$countryFacturation];
                //servira pour le taux TVA de transport
            } else {
                if($codeRegime == 'EI') {
                    $tauxStandardTVA = 0;
                } else {
                    $tauxStandardTVA = 19.6;
                }
            }

            //entête du ticket : ligne ET
            $seconds  = 0;
            $datetime = $data['created_at'];
            $parts = explode(' ', $datetime);
            if (isset($parts[1])){
                $timeparts = explode(':', $parts[1]);
                if (count($timeparts) == 3){
                    $seconds = (3600 * $timeparts[0]) + (60 * $timeparts[1]) + $timeparts[2];
                }
            }

            $shippingAmountTTCwithDiscount = round(
                ( $data['base_shipping_incl_tax']
                //    - $data['base_shipping_discount_amount'][$feed]]//TODO
                ), 2);

            $lines[] = array(  1 => 'TV'     //Type de ligne
                            ,  2 => $souche  //Souche
                            ,  3 => $numero  //Numéro de ticket
                            ,  4 => $societe //Société
                            ,  5 => 'ET'     //Sous-type de ligne
                            ,  6 => EaiMapper::dateYMD2DMY($data['created_at']) //Date sans heure
                            ,  7 => '0002'   //Code magasin
                            ,  8 => '' //$order['customer_id'] //Code Client détail  Normalement ce champ sert a stocker le code client détail (id fidélité si suivit dans Colombus) donc il faudrait qu’il soit vide.
                            ,  9 => 'EUR'//$order['order_currency_code'] //EUR' //Code devise
                            , 10 => '1'      //Taux conversion 1
                            , 11 => '1'      //Taux conversion 2
                            , 12 => '0'      //Type conversion 2
                            , 13 => 'VE'     //Catégorie de document
                            , 14 => $codeRegime //Régime de facturation
                            , 15 => $vendeur //Code Vendeur
                            , 16 => ''       //Code saison
                            , 17 => 'PVEUF'  //Code Tarif
                            , 18 => ''       //Code Evénement promotionnel
                            , 19 => $seconds //EaiMapper::dateYMD2DMY($order['created_at'], false, true) //DateHeure de validation
                            , 20 => $libelle //Libellé du document //Format : (numéro de la commande Internet) <numéro de cartes fidélité entre crochets si le client l’a utilisé pour l’achat> [identifiant client internet] @INTER pour commandes issues du site, @CALL pour commande crées par le service client, @IPHON pour les commandes crées par l’application Iphone ex : (789226M1) <2559010097893> [86881] @INTER
                            , 21 => ''       //tab après la dernière colonne (donc une tabulation). Ces colonnes ont été ajoutées lors du passage au format v7.
                            , 22 => ''
                            , 23 => ''
            );


            //******************* lignes du ticket, LT *********************************

            $numline = 1;
            $mtCoupon = 0;

            //$totalDiscount = abs(round($orderProduct['discount_invoiced'], 2));

            foreach ($data['items'] as $index => $orderItem) {

                $productId = $orderItem['product_id'];
                $qty = round($orderItem['qty'], 0);

                $tvaPercent = round($orderItem['tax_percent'], 1); //format : 19.6 par exemple

				$codeTVA = '';
                //todo gérer ca par pays de livraison
                if (strtotime($data['created_at']) < strtotime('2013-01-01 00:00:00')  or !isset($trancoTauxStandardTVA[$countryFacturation])) {
					dump("trancoTauxStandardTVA[$countryFacturation] ", $trancoTauxStandardTVA[$countryFacturation], $countryFacturation);
					if ($tvaPercent > 7) {
					        $codeTVA = 1;  // TVA standard
				    } elseif ($tvaPercent > 5.5) {
				        $codeTVA = 3;   // TVA reduite 2 pour les livres
				    } elseif ($tvaPercent > 0) {
				        $codeTVA = 2;   // TVA reduite 2 pour les jouets
				    } elseif ($tvaPercent == 0) {
						if ($codeRegime == 'EI') {
					        $codeTVA = 1;   
						} else {
							$codeTVA = 0;	// Pas de TVA
						}
				    }
				} else {
				    if ($tvaPercent > $trancoTauxLivreTVA[$countryFacturation]) {
				        $codeTVA = 1;  // TVA standard
				    } elseif ($tvaPercent > $trancoTauxReduitTVA[$countryFacturation]) {
				        $codeTVA = 3;   // TVA reduite 2 pour les livres
				    } elseif ($tvaPercent > 0) {
				        $codeTVA = 2;   // TVA reduite 2 pour les jouets
				    } elseif ($tvaPercent == 0) {
						if ($codeRegime == 'EI') {
					        $codeTVA = 1;   
						} else {
							$codeTVA = 0;	// Pas de TVA
						}
				    } else {
				        $codeTVA = '';
				        $obj->log("Le code TVA pour le pays '$countryFacturation' a $tvaPercent % n'est pas implementé", 'err');
				    }
				}
                //prix de base UNITAIRE TTC SANS discount amount
                $baseUnitPrice = round($orderItem['base_price_incl_tax'] ,2); //todo rajouter discount ?

                $regularUnitPrice = round($orderItem['regular_unit_price_incl_tax'], 2);
                //todo récupérer infos
                
                //$statutPromo normal    special basket  coupon  soldes  promo
                //jointure sur la table ocp : on va avoir un $regularPrice et un $codePromo



                //$regularPrice vient de la db
                //$pu = ($orderItem['base_row_invoiced'] + $orderItem['tax_invoiced']) / $qty;
                if (round($regularUnitPrice,2) < round($baseUnitPrice,2)) {
                    $obj->log("Incohérence : regularUnitPrice ($regularUnitPrice) < baseUnitPrice ($baseUnitPrice)", 'err');
                    $regularUnitPrice = $baseUnitPrice;
                }
                $pu = $regularUnitPrice;
                $codeRemiseColombus = '';
                $mtRemiseColombus = 0;//si != 0 alors il doit y avoir un code Remise
                $codeEventColombus  = 'PB';//par défaut PB, en cas de solde H12D1, H12D2...
                $deltaPrice = max(0, ($regularUnitPrice - $baseUnitPrice));
                //Alterations suivant le type de paiement
                $mtDiscountLine = abs($orderItem['base_discount_amount']);

                /*if (isset($orderItem['promotion_type'])
                    && $orderItem['promotion_type'] == 'solde'
                ) {
                    $promotionType = $orderItem['promotion_type'];
                    //'promotion' (catalogue) ou 'solde' ou 'normal' ou vide. Le statut 'promotion' n'est pas assez fiable car pour colombus, "promotion" peut eter aussi une promo panier non-coupon ou un prix spécial
                } else {
                    $promotionType = null;
                }*/

                $catalogRuleCodes = explode(',', $orderItem['applied_catalog_rule_codes']);
                $basketRuleCodes  = explode(',', $orderItem['applied_basket_rule_codes']);
                //Définition d'une regle catalogue par défaut
                //dump($catalogRuleNames, $basketRuleNames);exit();
                $mainCatalogRule = '';
                if (count($catalogRuleCodes) == 1 && !empty($catalogRuleCodes[0])) {
                    $mainCatalogRule = trim($catalogRuleCodes[0]);
                } else {
                    if (count($basketRuleCodes) == 1 && !empty($basketRuleCodes[0])) {
                        $mainCatalogRule = trim($basketRuleCodes[0]);
                    } elseif (count($catalogRuleCodes) > 1 || count($basketRuleCodes) > 1) {
                        $mainCatalogRule = $keywordDefaultPromo;
                    }
                }
                //Gestion des regles promo catalog / fiche produit


                //$typeEvents = Gestion des promotions, tous types croisés entre magento et colombus
                //  solde   coupon  promo_basket    promo_catalog   special
                $typeEvents = array();//contient les types de réduction colombus : promotion, soldes, coupon

                //Gestion des promotions catalogue
                if (strpos($mainCatalogRule, $keywordSolde) !== false /*$promotionType == 'solde'*/) {
                    $typeEvents['solde'] = true;
                } else {
                    if ($deltaPrice > 0) {
                        //ya une diff entre prix de base et regulier => promo catalogue ou prix special
                        //pour colombus : solde et/ou promotion (normalement les 2 en meme temps sont impossible)
                        $typeEvents['promo_catalog'] = true;//TODO voir si 'special' inclus dans ce cas
                    }


                }
                //var_dump($coupon);
                //kill($orderItem['base_discount_amount']." paaaaaaaaaanier ".$coupon);
                if (!empty($orderItem['base_discount_amount'])) {
                    //Ya du discount = promo panier
                    //(pour colombus : coupon réduction et/ou promotion sont gérés différements
                    if ($coupon) {
                        if (strlen(str_replace(array(',',' '), '', $orderItem['applied_rule_ids'])) > 0) {
                            $typeEvents['promo_basket'] = true;
                        } else {
                            $typeEvents['coupon'] = true;
                        }
                        //$typeEvents['promo_basket'] = true;
                    } else {    //TODO les 2 cas seront possibles et pas exclusifs
                        //dump($index, "xxxxxxx", $orderItem['base_discount_amount'][$feed]]);
                        //if (!isset($typeEvents['solde'])) {
                            $typeEvents['promo_basket'] = true;
                        //}
                    }
                }

                if (isset($typeEvents['solde'])){
                    $pu = $baseUnitPrice;
                    $codeEventColombus = str_replace($keywordSolde, '', $mainCatalogRule);//yora un pb si ya une promo appliqué sur un produit soldé
                    $codeRemiseColombus = '';
                } else {
                    $pu = $regularUnitPrice;
                    $codeEventColombus = 'PB';
                    $codeRemiseColombus = $mainCatalogRule;
                }

                if (isset($typeEvents['promo_catalog'])
                 || isset($typeEvents['special'])
                ) {    //traitement a faire qu'une seule fois
                    $mtRemiseColombus+= $deltaPrice*$qty;
                }

                if (isset($typeEvents['special'])){
                    $codeRemiseColombus = 'SHP';//priorité
                }

                //règles promo panier

                if (isset($typeEvents['coupon'])) {
                    $mtCoupon+= $mtDiscountLine;  //TODO récupérer un montant spécifique au discount
                } elseif (isset($typeEvents['promo_basket'])
                    //|| isset($typeEvents['coupon']) //solution temporaire : on met le mt coupon au meme endroit
                ) {
                    $mtRemiseColombus+= $mtDiscountLine;//TODO faut récup le mt dissocié du coupon et gérer le frais de port
                }


                if($mtRemiseColombus && empty($codeRemiseColombus)) {
                    $codeRemiseColombus = $mainCatalogRule;//'OP2';//code remise par défaut
                }

                //Cas particuliers :
                if (empty($typeEvents) && $orderItem['base_discount_amount']){
                    $obj->log("Attention pas de typeEvent mais du discount sur $mixedId mtRemiseColombus= $mtRemiseColombus base_discount_amount={$orderItem['base_discount_amount']}", 'warn');
                    if ($mtRemiseColombus) {
                        $mtRemiseColombus = $orderItem['base_discount_amount'];
                    }
                    //$codeRemiseColombus = $mainCatalogRule;
                }

                if (strpos($codeRemiseColombus, $keywordSolde) !== false) {
                    $codeRemiseColombus = $keywordDefaultPromo;
                }
                if (empty($mtRemiseColombus) && $codeRemiseColombus == $keywordDefaultPromo) {
                    $codeRemiseColombus = null;
                }


                /*dump( '$typeEvents '.$index, $typeEvents
                    , '$mtCoupon', $mtCoupon
                    , 'coupon', $coupon
                    , '$regularPrice', $regularUnitPrice
                    , '$baseUnitPrice', $baseUnitPrice
                    , '$deltaPrice', $deltaPrice
                    , 'base_discount_amount', $orderItem['base_discount_amount']
                    , '$mtDiscountLine', $mtDiscountLine
                    , '$mtRemiseColombus', $mtRemiseColombus
                    , '$codeRemiseColombus', $codeRemiseColombus
                    , '$codeEventColombus', $codeEventColombus
                    , '$pu', $pu
                    , '$mainCatalogRule', $mainCatalogRule
                );*/

                $lines[] = array(  1 => 'TV'     //Type de ligne
                            ,  2 => $souche  //Souche
                            ,  3 => $numero  //Numéro
                            ,  4 => $societe //Société
                            ,  5 => 'LT'     //Sous-type de ligne
                            ,  6 => $numline //Numéro de ligne
                            ,  7 => $vendeur //Code Vendeur
                            ,  8 => ''           //$orderProduct['sku'] //Code sku
                            ,  9 => $orderItem['ocp_ean'] //Code-à-barre, joint ure sur product, ocp_ean
                            , 10 => static::formatPrice($qty) //qty_ord ered //Quantité vendue //qty_ordered
                            , 11 => static::formatPrice($pu, 1)      //Prix unitaire TTC. Prix soldé dans le cas de soldes, sinon prix barré/originel.
                            , 12 => !empty($mtRemiseColombus) ? static::formatPrice($mtRemiseColombus) : '' //discount_amount //Montant remise hors-soldes et hors-coupon réduction (non-unitaire mais sur l'ensemble des quantités de la ligne). Magento la retourne en positif
                            , 13 => $codeRemiseColombus       //Code motif de remise si forcé
                            , 14 => $codeTVA //Code TVA
                            , 15 => $tvaPercent //Taux TVA format 99.999
                            , 16 => $codeEventColombus //Evénement promotionnel
                            , 17 => static::formatPrice($regularUnitPrice, 1) //Prix d'origine
                            , 18 => ''       //Code motif de retour
                            , 19 => ''       //tab apres derniere colonne
                );
                $numline++;

            }//foreach

            //      modes de règlement : LM (plusieurs lignes, oui c'est possible)


            //see https://docs.google.com/a/onlinecommercepartners.com/spreadsheet/ccc?key=0AvLo_KVOl601dFRhSkpmakNGU3plN19aRGFhNnRlOFE#gid=0
            //$p = $order['payment'];
            $totalPaymentTTC = $data['base_grand_total'];
            $method = $paymentMethods[$data['payment_method_id']];
            
            //TODO gérer leetchi, carte cadeaux DPAM
            //if ($data['base_customer_balance_amount'] && $data['base_customer_balance_amount'] != 0) {
            //if ($order['base_customer_balance_invoiced']) {

                //dump('$mtAvoir='.$mtAvoir,$mtAvoir);
            if (isset($data['base_customer_balance_amount'])
                   && $data['base_customer_balance_amount']
                   && static::$feed == 'invoiced'
            ) {
                $mtAvoir = $data['base_customer_balance_amount'];

                if((int)$mtAvoir != 0) {
                    $lines[] = array(  1 => 'TV'     //Type de ligne
                                    ,  2 => $souche  //Souche
                                    ,  3 => $numero  //Numéro
                                    ,  4 => $societe //Société
                                    ,  5 => 'LM'     //Sous-type de ligne
                                    ,  6 => 'AR'  //Code mode de règlement
                                    ,  7 => 'EUR'    //$order['order_currency_code'] //Code devise
                                    ,  8 => '<?>'    //Taux conversion 1
                                    ,  9 => '<?>'    //Taux conversion 2
                                    , 10 => '<?>'    //Type conversion 2
                                    , 11 => ''       //Code échéance
                                    , 12 => EaiMapper::dateYMD2DMY($data['created_at']) //Date du règl.
                                    , 13 => static::formatPrice($mtAvoir)   //Montant du règl.
                                    , 14 => 'True'//($order['base_subtotal_invoiced']) ? 'True' : 'False'        //Règlement perçu o/n
                                    , 15 => ''       //tab apres derniere colonne
                    );
                    //$totalPaymentTTC -= $mtAvoir;
                }
            }


            if (isset($data['base_customer_balance_total_refunded'])
                   && $data['base_customer_balance_total_refunded']
            ) {
                $mtAvoirRefunded = $data['base_customer_balance_total_refunded'];

                if((int)$mtAvoirRefunded != 0) {
                    $lines[] = array(  1 => 'TV'     //Type de ligne
                                    ,  2 => $souche  //Souche
                                    ,  3 => $numero  //Numéro
                                    ,  4 => $societe //Société
                                    ,  5 => 'LM'     //Sous-type de ligne
                                    ,  6 => 'AV'  //Code mode de règlement
                                    ,  7 => 'EUR'    //$order['order_currency_code'] //Code devise
                                    ,  8 => '<?>'    //Taux conversion 1
                                    ,  9 => '<?>'    //Taux conversion 2
                                    , 10 => '<?>'    //Type conversion 2
                                    , 11 => ''       //Code échéance
                                    , 12 => EaiMapper::dateYMD2DMY($data['created_at']) //Date du règl.
                                    , 13 => static::formatPrice($mtAvoirRefunded)   //Montant du règl.
                                    , 14 => 'True'//($order['base_subtotal_invoiced']) ? 'True' : 'False'        //Règlement perçu o/n
                                    , 15 => ''       //tab apres derniere colonne
                                    );
                    //$totalPaymentTTC -= $mtAvoir;
                }
            }



            if ((int)$totalPaymentTTC != 0) {
                if($method != 'paypal') {
                    //Hors Paypal
                    $lines[] = array(  1 => 'TV'     //Type de ligne
                                    ,  2 => $souche  //Souche
                                    ,  3 => $numero  //Numéro
                                    ,  4 => $societe //Société
                                    ,  5 => 'LM'     //Sous-type de ligne
                                    ,  6 => $method  //Code mode de règlement
                                    ,  7 => 'EUR'    //$order['order_currency_code'] //Code devise
                                    ,  8 => '<?>'    //Taux conversion 1
                                    ,  9 => '<?>'    //Taux conversion 2
                                    , 10 => '<?>'    //Type conversion 2
                                    , 11 => ''       //Code échéance
                                    , 12 => EaiMapper::dateYMD2DMY($data['created_at']) //Date du règl.
                                    , 13 => static::formatPrice($totalPaymentTTC)   //Montant du règl.
                                    , 14 => 'True'//($order['base_subtotal_invoiced']) ? 'True' : 'False'        //Règlement perçu o/n
                                    , 15 => ''       //tab apres derniere colonne
                    );
                } else {
                    //Il faut séparer la commission pour paypal
                    $commission = round((0.25 + (0.014*$totalPaymentTTC)), 2);    // 0.25 Euro + 1.4% du règlement)

                    $lines[] = array(  1 => 'TV'     //Type de ligne
                                    ,  2 => $souche  //Souche
                                    ,  3 => $numero  //Numéro
                                    ,  4 => $societe //Société
                                    ,  5 => 'LM'     //Sous-type de ligne
                                    ,  6 => 'PPC'    //partie commission d'un règlement paypal (0.25 Euro + 1,4% du règlement)//Code mode de règlement
                                    ,  7 => 'EUR'    //$order['order_currency_code'] //Code devise
                                    ,  8 => '<?>'    //Taux conversion 1
                                    ,  9 => '<?>'    //Taux conversion 2
                                    , 10 => '<?>'    //Type conversion 2
                                    , 11 => ''       //Code échéance
                                    , 12 => EaiMapper::dateYMD2DMY($data['created_at']) //Date du règl.
                                    , 13 => static::formatPrice($commission) //Montant du règl.
                                    , 14 => 'True'//($order['base_subtotal_invoiced']) ? 'True' : 'False'        //Règlement perçu o/n
                                    , 15 => ''       //tab apres derniere colonne
                    );
                    if (static::$feed == 'invoiced') {
                        $lines[] = array(  1 => 'TV'     //Type de ligne
                                        ,  2 => $souche  //Souche
                                        ,  3 => $numero  //Numéro
                                        ,  4 => $societe //Société
                                        ,  5 => 'LM'     //Sous-type de ligne
                                        ,  6 => 'PPN'    //PPN - partie net d'un règlement paypal PPN - partie net d'un règlement paypal (règlement - PPC)//Code mode de règlement
                                        ,  7 => 'EUR'    //$order['order_currency_code'] //Code devise
                                        ,  8 => '<?>'    //Taux conversion 1
                                        ,  9 => '<?>'    //Taux conversion 2
                                        , 10 => '<?>'    //Type conversion 2
                                        , 11 => ''       //Code échéance
                                        , 12 => EaiMapper::dateYMD2DMY($data['created_at']) //Date du règl.
                                        , 13 => static::formatPrice($totalPaymentTTC - $commission) //Montant du règl.
                                        , 14 =>'True'// ($order['base_subtotal_invoiced']) ? 'True' : 'False'        //Règlement perçu o/n
                                        , 15 => ''       //tab apres derniere colonne
                        );
                    }
                }
            }//if $totalPaymentTTC

            if ($mtCoupon) {
                //todo rajouter mt frais de port rémisé

                $lines[] = array(  1 => 'TV'     //Type de ligne
                                ,  2 => $souche  //Souche
                                ,  3 => $numero  //Numéro
                                ,  4 => $societe //Société
                                ,  5 => 'LM'     //Sous-type de ligne
                                ,  6 => 'BF' //Code mode de règlement
                                ,  7 => $data['order_currency_code'] //Code devise
                                ,  8 => '<?>'    //Taux conversion 1
                                ,  9 => '<?>'    //Taux conversion 2
                                , 10 => '<?>'    //Type conversion 2
                                , 11 => ''       //Code échéance
                                , 12 => EaiMapper::dateYMD2DMY($data['created_at']) //Date du règl.
                                , 13 => $mtCoupon //Montant du règl.    //$sign
                                , 14 => 'True'//($order['base_subtotal_invoiced']) ? 'True' : 'False'        //Règlement perçu o/n
                                , 15 => ''       //tab apres derniere colonne
                                );
            }

            /*
             * Codes Mouvements Financiers LF
             *   HH - pour le versement d'arrhes par le client
             *   RR - pour la déduction d'arrhes par le client
             *   TIC - pour les frais de transport UE
             *   TIH - pour les frais de transport hors UE
             *   XY - pour les arrondis
             * 
             */

            //Frais de port : en mode LF (se placent apres ligne LM)
            //see https://docs.google.com/a/onlinecommercepartners.com/spreadsheet/ccc?key=0AvLo_KVOl601dE1VN3B6SlJyN0RyZHNfX2dKUTliN3c#gid=1

            if(1 or $feed == 'invoiced' || $feed == 'ordered') {
                $lines[] = array(  1 => 'TV'     //Type de ligne
                                ,  2 => $souche  //Souche
                                ,  3 => $numero  //Numéro
                                ,  4 => $societe //Société
                                ,  5 => 'LF'     //Sous-type de ligne
                                ,  6 => 'TIC'    //Code mouvement financier, a mieux gérer si on livre hors UE
                                ,  7 => static::formatPrice($shippingAmountTTCwithDiscount)//TODO  comment gérer les remises ? shipping_amount //Mt du mouvement. magento renvoie le ttc
                                ,  8 => 1        //Code TVA. On considèrera que les fdp sont en tva standard
                                ,  9 => $tauxStandardTVA //Taux TVA
                                , 10 => ''       //tab apres derniere colonne
                );
            }


            //TODO gestion des avoirs (code AV) sur les lignes LM


            //ligne CU coupon de reduction

            if ($coupon) {
                $lines[] = array(  1 => 'TV'     //Type de ligne
                                ,  2 => $souche  //Souche
                                ,  3 => $numero  //Numéro de ticket
                                ,  4 => $societe //Société
                                ,  5 => 'CU'     //Sous-type de ligne
                                ,  6 => $coupon ? 124 : '' //Numéro du champs utilisateur
                                ,  7 => $coupon  // Valeur du champs utilisateur//TODO coupon_code ?
                                ,  8 => ''       //tab apres derniere colonne
                );
            }
            //TODO ligne LF financiere pour liste de naissance

        } else {
            $obj->log("order ID $mixedId  ({'grand_total'} : {$data['grand_total']}) <= 0, skipped");
        }

        //dump($lines);
		$obj->setElement($lines);//mutiples
	}


}//class

