<pre>
<?php

include_once 'esb.php';

// Validation XML over xsd
// xmllint  /var/www/dev_esb/var/partners/antidot/product/catalog-DPAM-FR.xml --schema /var/www/dev_esb/etc/partners/antidot/xsd/afs-store_Catalog.xsd --noout

$xml= "/var/www/dev_esb/var/feeds/out/antidot/category.xml";
$xsd= '/var/www/dev_esb/etc/partners/antidot/xsd/afs-store_Categories.xsd';

$xml= "/var/www/dev_esb/var/partners/antidot/product/catalog-DPAM-fr.xml";
$xsd= '/var/www/dev_esb/etc/partners/antidot/xsd/afs-store_Catalog.xsd';


dump($xml,$xsd);

$xmlObj= new DOMDocument(1, 'UTF-8');
$xmlObj->load($xml);
$xmlString= $xmlObj->saveXml();

// dump($xmlString);
// exit;

validateOverXsd($xmlString, $xsd);
// validateOverXsd($xmlString, '/home/vpietri/Documents/antidot/AFS_Store-v2.0-Implementers/AFS_Store-v2.0-Implementers/xsd/afs-store_CatalogSkeleton.xsd');

function validateOverXsd($xmlString, $xsdPath)
{
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();

    $dom->loadXML($xmlString);
    if ($dom->schemaValidate($xsdPath)) {
        dump("Ok");
        $r = true;
    } else 	{
        //see http://www.php.net/manual/fr/domdocument.schemavalidate.php#62032
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            dump("Error (level:".$error->level.") ".trim($error->message), 'err');
        }
        libxml_clear_errors();
    }

    libxml_use_internal_errors(false);
}


?>
</pre>