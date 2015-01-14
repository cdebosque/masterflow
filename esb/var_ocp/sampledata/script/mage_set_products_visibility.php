<pre><?php
/*
 * Gestion de la visibilité des produits configurables
 */

///require_once '/home/jaymard/www/dev_ecpm/app/Mage.php';
require_once '/var/www/dpam-formation/app/Mage.php';


Mage::app();

$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');


$statement = $read->query("SELECT attribute_id FROM eav_attribute WHERE attribute_code='visibility'");
$result = $statement->fetch();
$visibilityAttributeId = $result['attribute_id'];

$statement = $read->query("SELECT attribute_id FROM eav_attribute WHERE attribute_code='ocp_color'");
$result = $statement->fetch();
$colorAttributeId = $result['attribute_id'];

$statement = $read->query("SELECT count(1) as nb FROM core_store");
$result = $statement->fetch();
$nbStore = $result['nb'];


// On cache tous les produits configurables
$read->query("UPDATE catalog_product_entity_int p
  JOIN catalog_product_super_link l ON l.parent_id = p.entity_id
  SET p.value = ".Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH."
    WHERE attribute_id = ".$visibilityAttributeId);

for ($i=1; $i <= $nbStore; $i++) {
  $read->query("UPDATE catalog_product_flat_$i
    SET visibility = ".Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH."
    WHERE type_id = 'configurable'");
}

// On récupere un produit simple en stock par couleur par produit configurable
$statement = $read->query("SELECT i.product_id
  FROM catalog_product_entity_int p
  JOIN catalog_product_super_link l ON l.product_id = p.entity_id
  JOIN catalog_product_entity_int p2 ON p.entity_id = p2.entity_id
  AND p2.attribute_id = ".$colorAttributeId."
  JOIN cataloginventory_stock_item i ON i.product_id = p2.entity_id
  AND i.`is_in_stock` > 0
  WHERE p.attribute_id = ".$visibilityAttributeId." GROUP BY l.parent_id, p2.value");

$productsId = array();
while ($result = $statement->fetch()) {
  $productsId[] = $result['product_id'];
  if (count($productsId) > 500) {
    sendUpdateQuery($productsId, $nbStore, $visibilityAttributeId);
    unset($productsId);
  }
}

if ($productsId) {
  sendUpdateQuery($productsId, $nbStore, $visibilityAttributeId);
}

function sendUpdateQuery($productsId, $nbStore, $visibilityAttributeId) {
  $resource = Mage::getSingleton('core/resource');
  $read = $resource->getConnection('core_read');
  $read->query("UPDATE catalog_product_entity_int
        SET value = ".Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH."
        WHERE attribute_id = ".$visibilityAttributeId." AND entity_id IN (".implode(",", $productsId).")");
  for ($i=1; $i <= $nbStore; $i++) {
    $read->query("UPDATE catalog_product_flat_$i
      SET visibility = ".Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH."
      WHERE entity_id IN (".implode(",", $productsId).")");
  }
}

// mise a jour des tables flat


?>
