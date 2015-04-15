
-- Generation du fichier
dpam_mapping_for_code.csv

SELECT v.code_colombus, GROUP_CONCAT(DISTINCT REPLACE(g.groups_text,', jean','') ORDER BY g.groups_text DESC SEPARATOR ",")
FROM opt_to_groups og
JOIN groups_description g ON og.groups_id = g.groups_id
JOIN products_options_values v ON v.products_options_values_id = og.opt_id AND g.language_id=v.language_id
WHERE g.language_id=4 -- and v.code_colombus='1/2'
-- ORDER BY v.code_colombus, g.groups_id, v.language_id ASC
GROUP BY v.code_colombus
