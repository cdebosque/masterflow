
-- Generation du fichier
media_catalog_mapping_final.csv

SELECT  LOWER(i.products_image_A), CONCAT(p.products_model,'-', v.code_colombus,'-', 'A', '.jpg')
FROM products p
JOIN `products_images` i ON p.products_id = i.products_id
JOIN products_options_values v ON v.products_options_values_id = i.`products_attributes_id_1`
WHERE i.products_image_A IS NOT NULL
GROUP BY p.products_model, v.code_colombus
UNION
SELECT  LOWER(i.products_image_E), CONCAT(p.products_model,'-', v.code_colombus,'-', 'E', '.jpg')
FROM products p
JOIN `products_images` i ON p.products_id = i.products_id
JOIN products_options_values v ON v.products_options_values_id = i.`products_attributes_id_1`
WHERE i.products_image_E IS NOT NULL
GROUP BY p.products_model, v.code_colombus
UNION
SELECT  LOWER(i.products_image_DM), CONCAT(p.products_model,'-', v.code_colombus,'-', 'DM', '.jpg')
FROM products p
JOIN `products_images` i ON p.products_id = i.products_id
JOIN products_options_values v ON v.products_options_values_id = i.`products_attributes_id_1`
WHERE i.products_image_DM IS NOT NULL
GROUP BY p.products_model, v.code_colombus
UNION
SELECT  LOWER(i.products_image_D1), CONCAT(p.products_model,'-', v.code_colombus,'-', 'D1', '.jpg')
FROM products p
JOIN `products_images` i ON p.products_id = i.products_id
JOIN products_options_values v ON v.products_options_values_id = i.`products_attributes_id_1`
WHERE i.products_image_D1 IS NOT NULL
GROUP BY p.products_model, v.code_colombus
UNION
SELECT  LOWER(i.products_image_D2), CONCAT(p.products_model,'-', v.code_colombus,'-', 'D2', '.jpg')
FROM products p
JOIN `products_images` i ON p.products_id = i.products_id
JOIN products_options_values v ON v.products_options_values_id = i.`products_attributes_id_1`
WHERE i.products_image_D2 IS NOT NULL
GROUP BY p.products_model, v.code_colombus
UNION
SELECT  LOWER(i.products_image_D3), CONCAT(p.products_model,'-', v.code_colombus,'-', 'D3', '.jpg')
FROM products p
JOIN `products_images` i ON p.products_id = i.products_id
JOIN products_options_values v ON v.products_options_values_id = i.`products_attributes_id_1`
WHERE i.products_image_D3 IS NOT NULL
GROUP BY p.products_model, v.code_colombus
UNION
SELECT  LOWER(i.products_image_D4), CONCAT(p.products_model,'-', v.code_colombus,'-', 'D4', '.jpg')
FROM products p
JOIN `products_images` i ON p.products_id = i.products_id
JOIN products_options_values v ON v.products_options_values_id = i.`products_attributes_id_1`
WHERE i.products_image_D4 IS NOT NULL
GROUP BY p.products_model, v.code_colombus



-- Liste des sku des produits simple non associés à un produit configurable
SELECT cpe.sku 
FROM catalog_product_entity cpe
LEFT JOIN catalog_product_super_link cpsl ON cpe.entity_id=cpsl.product_id
LEFT JOIN catalog_product_entity_media_gallery cpemg ON cpe.entity_id=cpemg.entity_id
WHERE cpsl.product_id IS NULL AND cpe.type_id='simple' AND cpemg.value IS NULL

-- Liste des produits configurable sans images
SELECT cpe.sku
FROM catalog_product_entity cpe
LEFT JOIN catalog_product_entity_media_gallery cpemg ON cpe.entity_id=cpemg.entity_id
WHERE cpemg.value IS NULL AND  cpe.type_id='configurable';



-- Liste des sku des produits simple non associés à un produit configurable
SELECT cpe.sku 
FROM catalog_product_entity cpe
LEFT JOIN catalog_product_super_link cpsl ON cpe.entity_id=cpsl.product_id
LEFT JOIN catalog_product_entity_media_gallery cpemg ON cpe.entity_id=cpemg.entity_id
WHERE cpsl.product_id IS NULL AND cpe.type_id='simple' AND cpemg.value IS NULL

-- Liste des produits configurable sans images
SELECT cpe.sku
FROM catalog_product_entity cpe
LEFT JOIN catalog_product_entity_media_gallery cpemg ON cpe.entity_id=cpemg.entity_id
WHERE cpemg.value IS NULL AND  cpe.type_id='configurable';
