<?php
class EaiObserverProduct extends EaiObserverCore
{
    // Product Set
    static $productSetByKeys=array();

    static $productSetByIds=array();

    // Category
    static $categoriesByIds=array();

    static $categoriesByFullPath=array();

    static $categoriesSep= ';;';

    static $allowProductV2Attributes = array("categories",
            "websites",
            "name",
            "description",
            "short_description",
            "weight",
            "status",
            "url_key",
            "url_path",
            "visibility",
            "category_ids",
            "website_ids",
            "has_options",
            "gift_message_available",
            "formatPrice",
            "special_price",
            "special_from_date",
            "special_to_date",
            "tax_class_id",
            "tier_price",
            "meta_title",
            "meta_keyword",
            "meta_description",
            "custom_design",
            "custom_layout_update",
            "options_container",
            "additional_attributes");


    //-------------------------------------------------------------
    //-------- setProductSetByKeys<CONNECTOR> ---------------------
    /**
     *
     * @param EaiConnectorSoap $connector
     */
    public function setProductSetByKeysV1($connector)
    {
        $result = $connector->getClient()
                            ->call($connector->getSession()
                                    ,'product_attribute_set.list'
                            );

        self::setProductSet($result);
    }

    public function setProductSetByKeysV2($connector)
    {
        $result = $connector->getClient()
                            ->catalogProductAttributeSetList($connector->getSession());

        self::setProductSet($result);
    }

    public function setProductSetByKeysMage($connector)
    {
        $result = Mage::getModel('catalog/product_attribute_set_api')->items();

        self::setProductSet($result);
    }

    public function setProductSet($result)
    {
        foreach ($result as $resKey=>$resVal ) {
            if (is_object($resVal)) {
                $resVal = get_object_vars($resVal);
            }
            self::$productSetByKeys[self::cleanKeyName($resVal['name'])]= $resVal['set_id'];
            self::$productSetByIds[self::cleanKeyName($resVal['set_id'])]= $resVal['name'];
        }

    }
    //-------------------------------------------------------------
    //-------- setCategoriesByFullPath<CONNECTOR> ---------------------

    /**
     *
     * @param EaiConnectorSoap $connector
     */
    public function setCategoriesByFullPathV1($connector)
    {
        $categoryTree = $connector->getClient()
                                            ->call($connector->getSession()
                                                    ,'catalog_category.tree'
                                                    );

        self::setCategoriesByFullPath($categoryTree);
    }


    /**
     *
     * @param EaiConnectorSoap $connector
     */
    public function setCategoriesByFullPathV2($connector)
    {
        $categoryTree = $connector->getClient()
                                  ->catalogCategoryTree($connector->getSession());

        self::setCategoriesByFullPath($categoryTree);

    }

    public function setCategoriesByFullPathMage($connector,$parentId = null, $store = null)
    {
        $categoryTree = Mage::getModel('catalog/category_api')->tree($parentId,$store);

        self::setCategoriesByFullPath($categoryTree);
    }


    public function setCategoriesByFullPath($categoryTree)
    {
        self::$categoriesByIds= self::convertTreeToFlatArray($categoryTree);

        foreach (self::$categoriesByIds as $cateoryId=>$categoryInfo)
            self::$categoriesByFullPath[self::cleanKeyName($categoryInfo['full_path_name'])]= $categoryInfo;
    }



    //-------------------------------------------------------------
    //-------- Common methods ---------------------

    public function convertTreeToFlatArray($categoryTree,array $parentCategory=array(),array $treeInfo=array())
    {
        if (is_object($categoryTree)) {
            $categoryTree = get_object_vars($categoryTree);
        }
        $result= array();
        // Only basic category data

        if (empty($categoryTree['parent_id'])) {
            $fullPathName= $categoryTree['name'];
            $fullPathId  = $categoryTree['category_id'];
        } else {
            if (!empty($parentCategory)) {
                    $parentCategory['path_name']= trim($parentCategory['path_name'],'/').'/';
                    $parentCategory['path_id']= trim($parentCategory['path_id'],'/').'/';
            }

            $fullPathName= $parentCategory['path_name'].$categoryTree['name'];
            $fullPathId  = $parentCategory['path_id'].$categoryTree['category_id'];

            $treeInfo[]= array('id'=>$categoryTree['category_id'],
                'name'=>$categoryTree['name'], 'url_rewrite'=>$categoryTree['url_rewrite']);
        }


        $categoryInfo= array();
        foreach ($categoryTree as $attrKey=>$attrValue) {
            $categoryInfo[$attrKey]= $attrValue;
        }

        $categoryInfo['full_path_name'] = $fullPathName;
        $categoryInfo['full_path_id']   = $fullPathId;
        $categoryInfo['tree_info']      = $treeInfo;
        $categoryInfo['is_active']      = (isset($categoryTree['is_active']) ? $categoryTree['is_active'] : 1);
        if (!empty($categoryTree['parent_id'])) {
          $categoryInfo['parent_id']    = $categoryTree['parent_id'];
        }
        $categoryInfo['url_rewrite']      = (isset($categoryTree['url_rewrite']) ? $categoryTree['url_rewrite'] : '');

        $result[$categoryTree['category_id']] = $categoryInfo;


        if (!empty($categoryTree['children'])) {
            foreach ($categoryTree['children'] as $child) {

                $result= $result + self::convertTreeToFlatArray($child,
                                                                array('path_name'=>$fullPathName, 'path_id'=>$fullPathId),
                                                                $categoryInfo['tree_info']
                                                                );
            }
        }





        return $result;
    }

    /**
     * Retourne un tableau d'id de category
     *
     * @param string $categoriesPath
     * @param string $rootCategory
     */
    protected function getArrayIdsForCategoriesPath($categoriesPath, $rootCategory='')
    {

        $categoriesIds=array();

        $categoriesArray= explode(self::$categoriesSep,$categoriesPath);
        foreach ($categoriesArray as $categoryFullPath) {

            $searchCategory= $categoryFullPath;
            if (!empty($rootCategory))
                $searchCategory= trim($rootCategory,'/').'/'.$searchCategory;

            $searchCategory= self::cleanKeyName($searchCategory);
            if (isset(self::$categoriesByFullPath[$searchCategory])) {
                $categoriesIds[]= self::$categoriesByFullPath[$searchCategory]['category_id'];
            }

        }

        return $categoriesIds;
    }


    protected function cleanKeyName($str)
    {
        return strtolower($str);
    }

}