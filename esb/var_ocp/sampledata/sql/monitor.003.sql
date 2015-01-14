UPDATE `dataflow` SET enable = 1 WHERE enable IS NULL;
UPDATE `dataflow` SET enable = 0 WHERE name = 'partners/crosslog/purchase/importChangeV2';