<?php
class EaiObserverCore extends EaiObject
{

    // Store
    static $storesByIds=array();

    static $storesByLocales=array();

    static $storesByCodes=array();

    // Website
    static $websitesByIds=array();

    static $websitesByCodes=array();

    // Custom Array
    static $customArray = null;



    //-------------------------------------------------------------
    //-------- setStoresByIds<CONNECTOR> ---------------------


    public function setStoresByIdsMage($connector)
    {
        $result= Mage::getModel('core/store_api')->items();

        self::setStores($result);
    }

    public function setStoresByIdsV1($connector)
    {
        $result = $connector->getClient()
                            ->call($connector->getSession()
                                    ,'store.list'
                            );
        self::setStores($result);
    }


    public function setStores($result, $active=true)
    {
        foreach ($result as $store) {
            if (!$active or ($active and !empty($store['is_active'])) ) {
                self::$storesByIds[$store['store_id']] = $store;
                self::$storesByLocales[$store['locale']][$store['store_id']] = $store;
                self::$storesByCodes[$store['code']] = $store;
            }
        }
    }





    //-------------------------------------------------------------
    //-------- setWebsitesByIds<CONNECTOR> ---------------------

    public function setWebsitesByIdsMage($connector)
    {
        $result= Mage::getModel('core/website_api')->items();
        foreach ($result as $website) {
            //Ocp Hack pour éviter la russie
	    if($website['website_id']==8) continue;

            self::$websitesByIds[$website['website_id']] = $website;
            self::$websitesByCodes[$website['code']] = $website;
        }
    }

    public function setCustomArrayFromFile($file) {
      $handle = @fopen($file, "r");
      if ($handle) {
          while (($buffer = fgets($handle, 4096)) !== false) {
              self::$customArray[] = str_replace("\n", '', $buffer);
          }
          fclose($handle);
      }
    }

    /**
     * Positionnement des valeurs par locale des boutiques
     *
     * Attention necessite que self::$storesByLocales soit positionné
     *
     * @param array $rawDataStore
     * @param string $storeKey ('store_id' by default)
     * @param string $storeVal ('value' by default)
     */
    public function getValuesByStoreid(array $rawDataStore, $storeKey='store_id', $storeVal='value')
    {
        $values= array();

        if (!empty($rawDataStore['default'])) {
            $values[0]= $rawDataStore['default'];
        }
        elseif (!empty($rawDataStore['admin'])) {
            $values[0]= $rawDataStore['admin'];
        }

        foreach (self::$storesByLocales as $storeLocale=>$storeValues) {
            if (isset($rawDataStore[$storeLocale])) {
                foreach ($storeValues as $store) {
                    $values[$store['store_id']]= $rawDataStore[$storeLocale];
                }
            }
        }

        return $values;
    }

    /**
     * Positionnement des valeurs par locale des boutiques
     *
     * Attention necessite que self::$storesByLocales soit positionné
     *
     * @param array $rawDataStore
     * @param string $storeKey ('store_id' by default)
     * @param string $storeVal ('value' by default)
     */
    public function getValuesByStoreidKeyVal(array $rawDataStore, $storeKey='store_id', $storeVal='value')
    {
        $values= array();

        if (!empty($rawDataStore['default'])) {
            $values[]= array($storeKey=>0, $storeVal=>$rawDataStore['default']);
        }
        elseif (!empty($rawDataStore['admin'])) {
            $values[]= array($storeKey=>0, $storeVal=>$rawDataStore['admin']);
        }

        foreach (self::$storesByLocales as $storeLocale=>$storeValues) {
            if (isset($rawDataStore[$storeLocale])) {
                foreach ($storeValues as $store) {
                    $values[]= array($storeKey=>$store['store_id'], $storeVal=>$rawDataStore[$storeLocale]);
                }
            }
        }

        return $values;
    }


}
