<?php

$srcPath= '/home/vpietri/Téléchargements/missing_images/';
$dstPath= '/var/www/dev_esb/var/feeds/in/colombus/catalog/media/';

$file= "./media_catalog_mapping_final.csv";

$handle = fopen($file, 'r');
while (!feof($handle)) {
    $rawData = fgets($handle);
    $values = str_getcsv($rawData, ';', '"');

    //var_dump($values);


    $fileDesc= explode('.', $values[0]);

    $fileFrom= strtoupper($fileDesc[0]).'.jpg';


    if(!empty($fileFrom) and file_exists($srcPath.$fileFrom) ) {
        //var_dump($values);
        echo 'cp '.$srcPath.$fileFrom." ".$dstPath.$values[1].PHP_EOL;
        copy($srcPath.$fileFrom,$dstPath.$values[1]);
// exit;
    }
    //echo 'cp '.$srcPath.strtoupper($values[0])." ".$dstPath.$values[1].PHP_EOL;
//exit;
// 	if(file_exists($srcPath.$values[0]) and rename($srcPath.$values[0], $dstPath.$values[1])){
// 		//echo "Bad move: $srcPath.{$values[0]} ====> $dstPath.$values[1]";
// 	    echo "Move: $srcPath{$values[0]}".PHP_EOL;
// 	} else {
// 	    echo "Not exist: $srcPath{$values[0]}".PHP_EOL;
// 	}
// exit;

// 	2342  ls missing_images/
// 	2343  for remote in `cat sans_images_grp.txt`; do `cp catalog-prod/images/products/$remote/* missing_images/`; done
// 	2344  ls
// 	2345  ls catalog-prod/images/products/YHACKERG/
// 	2346  rm missing_images/thumb-*


// 	dev-vpietri:/var/www/dev_esb/etc/partners/colombus/products/images
//     var_dump($values);


//     if( strpos($values[0],'_')!==false)
// 	    $parts = explode('_', $values[0]);
//     else
//         $parts = explode('-', $values[0]);

//     var_dump($parts, count($parts));

// 	if(count($parts) != 3) {
// 		echo "bad parts: ".$values[0].PHP_EOL;
// 		exit;
// 		continue;
// 	}
// 	$colorCode = getColorCode($parts[1]);
// 	//print_r($parts);
// 	if(!$colorCode) {
// 		echo "bad label: ".$values[0].PHP_EOL;
// 		exit;
// 		continue;
// 	}
// 	$newImgName = $parts[0]."-$colorCode-".$parts[2];

// 	if(!rename($srcPath.$values[0], $dstPath.$newImgName)){
// 		echo "Bad move: $srcPath.{$values[0]} ====> $dstPath.$newImgName";
// 	}
}


 function getColorCode($color, $reverse = false)
	{

		$codeColors = array("ANIS/BLANC/BLEU"=>"ABBE",
			"ANIS/BLAN/PAILL"=>"ABPA",
			"ABRICOT"=>"ABRI",
			"ACIER"=>"ACIE",
			"ANIS/ECRU/MAUVE"=>"AEMV",
			"AMAN/FRAIS/LAGO"=>"AFLA",
			"ANTHR/GRIS/NOIR"=>"AGNO",
			"ANIS/KAKI/VERT"=>"AKVE",
			"ALBATROS BLANC"=>"ALBA",
			"AMANDE"=>"AMAN",
			"AMANDE/BLEU"=>"AMBE",
			"AMANDE/BRONZE"=>"AMBR",
			"AMETHYST"=>"AMET",
			"AMIRAL BLEU"=>"AMIR",
			"ANIS/MAUV/LAGON"=>"AMLA",
			"AMETHYSTE/PARME"=>"AMPA",
			"ANTHRACITE/BLEU"=>"ANBE",
			"ANIS CLAIR"=>"ANCL",
			"ANIS FONCE"=>"ANFO",
			"ANIS"=>"ANIS",
			"ANIS MOYEN"=>"ANMO",
			"ANTHRACITE/ROUG"=>"ANRG",
			"ANTHRACITE GRIS"=>"ANTH",
			"ANTHRACITE/VERT"=>"ANVE",
			"ABRIC/PAIL/ROUG"=>"APRG",
			"ARGENT/BLANC"=>"ARBL",
			"ARGENT/DORE"=>"ARDO",
			"ARGENT"=>"ARG",
			"ARGENT/ROUGE"=>"ARRG",
			"ASSORTIS"=>"ASSO",
			"AUBERGINE"=>"AUBE",
			"AZUR/NATUREL"=>"AZNA",
			"AZUR"=>"AZUR",
			"BANANE"=>"BANA",
			"BICOL_ANIS_CL"=>"BANC",
			"BICOL_ANIS_MO"=>"BANM",
			"BLANC/BLEU/BEIG"=>"BBBE",
			"BLEU/BLEUPA/ECR"=>"BBEC",
			"BICOL_BEIGE_FC"=>"BBEF",
			"BICOL_BEIGE_MO"=>"BBEM",
			"BICOL_BEIGE_CL"=>"BBGC",
			"BLANC/BLEU/JAUN"=>"BBJA",
			"BICOL_BLEU_CL"=>"BBLC",
			"BICOL_BLEU_FC"=>"BBLF",
			"BICOL_BLEU_MO"=>"BBLM",
			"BICOL_BORDO_FC"=>"BBOF",
			"BICOL_BRIQUE_FC"=>"BBRF",
			"BICOL_BRIQUE_MO"=>"BBRM",
			"BEIGE/BLEU/ROSE"=>"BBRO",
			"BLEUCLAI/BRIQUE"=>"BCBR",
			"BICOL_CIEL_MO"=>"BCEM",
			"BLEU/CIEL/MARIN"=>"BCIM",
			"BEIG/CIEL/MARIN"=>"BCMA",
			"BLANC/CIEL/ROSE"=>"BCRO",
			"BLANC/CIEL/SABL"=>"BCSA",
			"BLAN/DRAGE/GRIS"=>"BDGR",
			"BOIS DE ROSE"=>"BDRO",
			"BLEU/BLANC"=>"BEBL",
			"BLEU/CHOCOLAT"=>"BECH",
			"BLEU/CIEL"=>"BECI",
			"BEIGE CLAIR"=>"BECL",
			"BLEU/ECRU/MARIN"=>"BECM",
			"BLEU/ECRU"=>"BEEC",
			"BEIGE FONCE"=>"BEFC",
			"BEIGE CHINE"=>"BEGC",
			"BLEU GRIS"=>"BEGR",
			"BEIGE"=>"BEIG",
			"BLEU/JAUNE"=>"BEJA",
			"BEIGE/KAKI"=>"BEKA",
			"BELIER MOUTARDE"=>"BELI",
			"BEIGE/MARINE"=>"BEMA",
			"BEIGE MOYEN"=>"BEMO",
			"VERT BENETTON"=>"BENE",
			"BLEU/ORANGE"=>"BEOR",
			"BEIGE/PRUNE"=>"BEPR",
			"BLEU/ROUGE"=>"BERG",
			"BLEU/TURQUOISE"=>"BETU",
			"BLEUVERT"=>"BEVE",
			"BLEU/VERT"=>"BEVR",
			"BLANC/GRCH/BECH"=>"BGBC",
			"BEIGE/BLEU"=>"BGBE",
			"BEIGE/BLANC"=>"BGBL",
			"BEIGE/BRIQUE"=>"BGBR",
			"BEIGE/CHOCOLAT"=>"BGCH",
			"BEIGE/CIEL"=>"BGCI",
			"BEIGE/ECRU"=>"BGEC",
			"BEIGE/MARINE NE PLUS UTILISER"=>"BGMA",
			"BEIGE/MARRON"=>"BGMR",
			"BEIGE/ORANGE"=>"BGOR",
			"BICOL_GRIS_CL"=>"BGRC",
			"BICOL_GRIS_FC"=>"BGRF",
			"BICOL_GRIS_MO"=>"BGRM",
			"BEIGE/ROUGE"=>"BGRO",
			"BEIGE/ROSE"=>"BGRS",
			"BEIGE/VIOLET"=>"BGVI",
			"BICOL_ASSORTIS"=>"BIAS",
			"BICOL_BLANC"=>"BIBL",
			"BICOL_BORDEAUX"=>"BIBO",
			"BICOL_BRIQUE"=>"BIBR",
			"BICOLORE"=>"BICO",
			"BICOL_ECRU"=>"BIEC",
			"BICOL_FUCHSIA"=>"BIFU",
			"BICOL_MARINE"=>"BIMA",
			"BICOL_NOIR"=>"BINO",
			"BEIGE/TURQUOISE"=>"BITU",
			"BICOL_JAUNE_CL"=>"BJAC",
			"BICOL_JAUNE_FC"=>"BJAF",
			"BICOL_JAUNE_MO"=>"BJAM",
			"BLEU/JEAN/MARIN"=>"BJMA",
			"BLEU/JAUN/MARIN"=>"BJOM",
			"BICOL_KAKI_CL"=>"BKAC",
			"BICOL_KAKI_MO"=>"BKAM",
			"BICOL_KAKI FONC"=>"BKFO",
			"BLANC/KAKI/JAUN"=>"BKJA",
			"BEIG/KAKI/MARRO"=>"BKMR",
			"BLANC/AMETHYST"=>"BLAM",
			"BLANC"=>"BLAN",
			"AZUR/BLANC"=>"BLAZ",
			"BLANC/BLEU"=>"BLBE",
			"BLEU CHINE"=>"BLCH",
			"BLANC/CIEL"=>"BLCI",
			"BLEU/CIEL/MARIN"=>"BLCM",
			"BLEU CREPUSCULE"=>"BLCR",
			"BLANC/DRAGEE"=>"BLDR",
			"BLEACHED"=>"BLEA",
			"BLEU CLAIR"=>"BLEC",
			"BLEU"=>"BLEU",
			"BLEU FONCE"=>"BLFC",
			"BLANC/FRAMBOISE"=>"BLFM",
			"BLANC/FRAISE"=>"BLFR",
			"BLEU CIEL"=>"BLGR",
			"BLEU JEAN"=>"BLJE",
			"BLANC/JAUNE"=>"BLJO",
			"BLANC/LAVANDE"=>"BLLA",
			"BLEU/LILAS"=>"BLLI",
			"BLANC/MARINE"=>"BLMA",
			"BLEU MOYEN CHINE"=>"BLMC",
			"BLEU MOYEN"=>"BLMO",
			"BLANC/MAUVE"=>"BLMV",
			"BLEU/NOIR"=>"BLNO",
			"BLEU/OCEAN"=>"BLOC",
			"BLANC/ORANGE"=>"BLOR",
			"BLANC/PARME"=>"BLPA",
			"BLANC/ROSE"=>"BLRO",
			"BLANC/ROUGE"=>"BLRU",
			"BLANC/TABAC"=>"BLTA",
			"BLANC/TURQUOISE"=>"BLTU",
			"BLEU CHINE NE PLUS UTILISER"=>"BLUC",
			"BICOL_MARRON_MO"=>"BMA2",
			"BICOL_MAUVE_CL"=>"BMAC",
			"BICOL_MAUVE_FC"=>"BMAF",
			"BICOL_MAUVE_MO"=>"BMAM",
			"BEIG/MARIN/ROUG"=>"BMRG",
			"BEIGE/MARO/TURQ"=>"BMTU",
			"BLEU/MARRO/VERT"=>"BMVE",
			"BORDEAUX/BRIQUE"=>"BOBR",
			"BORDEAUX FONCE"=>"BOFC",
			"BORDEAUX/JEAN"=>"BOJE",
			"BORDEAUX/MARINE"=>"BOMA",
			"BORDEAUX/MARRON"=>"BOMR",
			"BORDEAUX/BEIGE"=>"BOR",
			"BICOL_ORANGE_CL"=>"BORC",
			"BORDEAUX"=>"BORD",
			"BICOL_ORANGE_FC"=>"BORF",
			"BICOL_ORANGE_MO"=>"BORM",
			"BORDEAUX/ROSE"=>"BORS",
			"VERT BOUTEILLE"=>"BOUT",
			"BLEU/ORANG/VERT"=>"BOVE",
			"BLANC/PRUNE/TUR"=>"BPTQ",
			"BRUT/CAMEL"=>"BRCA",
			"BRIQUE CLAIR"=>"BRCL",
			"BORDEAUX NE PLUS UTILISER"=>"BRD",
			"BRIQUE FONCE"=>"BRFC",
			"BRIQUE"=>"BRIQ",
			"BRIQUE MOYEN"=>"BRMO",
			"BRIQUE/NOIR"=>"BRNO",
			"BICOL_ROSE_MO"=>"BRO2",
			"BICOL_ROUGE_CL"=>"BROC",
			"BICOL_ROUGE_FC"=>"BROF",
			"BICOL_ROUGE_MO"=>"BROM",
			"BRONZE"=>"BRON",
			"BRUT/ROUGE"=>"BRRG",
			"BLANC/ROSE/SABL"=>"BRSA",
			"BRUN"=>"BRUN",
			"BRUT"=>"BRUT",
			"BRIQUE/VERT"=>"BRVE",
			"BOUTEILLE/MARIN"=>"BTMA",
			"BICOL_TURQUO_CL"=>"BTUC",
			"BICOL_TURQUOISE"=>"BTUR",
			"BLEU/GRIS"=>"BUGR",
			"BLEU/MARINE"=>"BUMA",
			"BICOL_VERT_CL"=>"BVEC",
			"BICOL_VERT_FC"=>"BVEF",
			"BICOL_VERT_MO"=>"BVEM",
			"BICOL_VIOLET_FC"=>"BVIF",
			"BICOL_VIOLET_MO"=>"BVIM",
			"CACAO"=>"CACA",
			"CARAMEL CLAIR"=>"CACL",
			"CACTUS"=>"CACT",
			"CANARD/ECRU"=>"CAEC",
			"CAMEL"=>"CAME",
			"CANARI"=>"CANA",
			"CANARD"=>"CAND",
			"CARMIN/NOIR"=>"CANO",
			"CARAMEL"=>"CARA",
			"CARMIN"=>"CARM",
			"CARMIN/SABLE"=>"CASA",
			"CASSIS"=>"CASS",
			"CERISE/ECRU"=>"CEEC",
			"CIEL/ECRU/GRIS"=>"CEGR",
			"CERISE"=>"CERI",
			"CIEL/ECRU/ROSE"=>"CERS",
			"CHAILLOT VERT"=>"CHAI",
			"CHAMPAGNE"=>"CHAM",
			"CHAIR"=>"CHAR",
			"CHATAIGNE"=>"CHAT",
			"CHOCOLAT/CIEL"=>"CHCI",
			"CHOCOLAT/ECRU"=>"CHEC",
			"CHOCOLAT/JAUNE"=>"CHJO",
			"CHOCOLAT"=>"CHOC",
			"CHOCOLAT/ROSE"=>"CHRO",
			"CHINE/SABLE"=>"CHSA",
			"CIEL CHINE"=>"CICH",
			"CIEL/DRAGEE"=>"CIDR",
			"CIEL/ECRU"=>"CIEC",
			"CIEL"=>"CIEL",
			"CIEL/GRIS"=>"CIGR",
			"CIEL/MARINE"=>"CIMA",
			"CIEL MOYEN"=>"CIMO",
			"CIEL/MARRON"=>"CIMR",
			"CIEL/NATUREL"=>"CINA",
			"CIEL/ROSE"=>"CIRO",
			"CITRON"=>"CITR",
			"CIEL/TRANSPAREN"=>"CITS",
			"CITRON VERT"=>"CIVE",
			"CLEMENTINE"=>"CLEM",
			"CORAIL MOYEN"=>"COMO",
			"CORAIL"=>"CORA",
			"CIEL/PRUNE/SABL"=>"CPSA",
			"CRAIE"=>"CRAI",
			"CHOCO/ROSE/VERT"=>"CRVE",
			"CITROUILLE"=>"CTRO",
			"DENIM BLACK"=>"DEBL",
			"DENIM CLAIR"=>"DECL",
			"DENIM FONCE"=>"DEFC",
			"DENIM GRIS"=>"DEGR",
			"DENIM MARRON"=>"DEMA",
			"DENIM MOYEN"=>"DEMO",
			"DENIM"=>"DENI",
			"DRAG/FRAIS/FUSH"=>"DFFU",
			"DORE"=>"DORE",
			"DOUBLE STONE"=>"DOUB",
			"DRAGE/ROS/PRUNE"=>"DPRO",
			"DRAGEE"=>"DRAG",
			"DRAGEE/ECRU"=>"DREC",
			"DRAGEE/PRUNE"=>"DRPR",
			"DRAGEE/ROSE"=>"DRRO",
			"lvi-dra/dra-lil"=>"DVLI",
			"EBENE"=>"EBEN",
			"ECRU/BLEU"=>"ECBL",
			"ECRU/FUSHIA"=>"ECFU",
			"ECR/GRICL/GRIFO"=>"ECGC",
			"GRIS/ECRU"=>"ECGR",
			"ECRU/LIE DE VIN"=>"ECLI",
			"ECRU/MARINE NE PLUS UTILISER"=>"ECMA",
			"ECRU/MARRON"=>"ECMR",
			"ECRU/MAUVE"=>"ECMV",
			"ECRU/NATUREL"=>"ECNA",
			"ECRU/OCEAN"=>"ECOC",
			"ECRU/ORANGE"=>"ECOR",
			"ECRU/PRUNE"=>"ECPR",
			"ECRU/ROSE"=>"ECRS",
			"ECRU"=>"ECRU",
			"ECRU/SABLE"=>"ECSA",
			"ECRU/VERMILLON"=>"ECVE",
			"EMERAUDE"=>"EMER",
			"ENZYME"=>"ENZY",
			"ECRU/ORANG/ROUG"=>"EORG",
			"EPICE"=>"EPIC",
			"FANTAISIE"=>"FAN",
			"FICELLE"=>"FICE",
			"FLEURS"=>"FLEU",
			"FUCHS/MAUVE/ROS"=>"FMRO",
			"FONTAINE VERT"=>"FONT",
			"FORET"=>"FORE",
			"FOUGERE"=>"FOUG",
			"FRAISE"=>"FRAI",
			"FRAMBOISE"=>"FRAM",
			"FRAISE/ROUGE"=>"FRRO",
			"FUCHSIA/FRAISE"=>"FUFR",
			"FUCHSIA/MAUVE"=>"FUMA",
			"FUCHSIA/ROSE"=>"FURO",
			"FUCHSIA/SABLE"=>"FUSA",
			"FUCHSIA"=>"FUSH",
			"FUCHSVERT"=>"FUVE",
			"GALET"=>"GALE",
			"GAZON"=>"GAZO",
			"GRIS CLAIR/ECRU"=>"GCEC",
			"GRIS FONCE/ECRU"=>"GFEC",
			"GITANE/ECRU"=>"GIEC",
			"GRIS PALE"=>"GIPA",
			"BLEU GITANE"=>"GITA",
			"GRIS/TURQUOISE"=>"GITU",
			"MARRON GOSPEL"=>"GOSP",
			"BLANC/GRIS"=>"GRBL",
			"GRIS CHINE"=>"GRCH",
			"GRIS CLAIR"=>"GRIC",
			"GRIS FONCE"=>"GRIF",
			"GRIS"=>"GRIS",
			"GRIS/MARINE"=>"GRMA",
			"GRIS MOYEN"=>"GRMO",
			"GRIS/NOIR"=>"GRNO",
			"GRIS/ORANGE"=>"GROR",
			"GRIS/PARME"=>"GRPA",
			"GRIS PERLE"=>"GRPE",
			"GRIS/ROUGE"=>"GRRO",
			"GRIS/SABLE"=>"GRSA",
			"GRIS/TURQUOISE"=>"GRTU",
			"GRIS VERT"=>"GRVE",
			"HERMES/ECRU"=>"HEEC",
			"HERMES"=>"HERM",
			"IMPRIME"=>"IMPR",
			"INCA"=>"INCA",
			"INCOLORE"=>"INCO",
			"INDIGO"=>"INDI",
			"INDIGO/ECRU"=>"INEC",
			"IRIS"=>"IRIS",
			"IVOIRE"=>"IVOI",
			"JAUNE CLAIR"=>"JACL",
			"JAUNE FONCE"=>"JAFC",
			"JAUNE FLUO"=>"JAFL",
			"GRIS/JAUNE"=>"JAGR",
			"JAUNE/KAKI"=>"JAKA",
			"JAUNE MOYEN"=>"JAMO",
			"JAUNE/ORANGE"=>"JAOR",
			"JAUNE PALE"=>"JAPA",
			"JAUNE/ROSE"=>"JARO",
			"JARRET"=>"JARR",
			"JAUNE"=>"JAUN",
			"JAUNE/VERT"=>"JAVE",
			"JEAN"=>"JEAN",
			"JEAN/BLEU"=>"JEBL",
			"JEAN/BRIQUE"=>"JEBR",
			"JEAN/MARINE"=>"JEMA",
			"JEAN/NOIR"=>"JENO",
			"JEAN/PRUNE"=>"JEPR",
			"JEAN/ROSE"=>"JERO",
			"JEAN/SABLE"=>"JESA",
			"JAUNE/KAKI/VERT"=>"JOKV",
			"JAUNE D'OR"=>"JOR",
			"JAUNE/ROUGE"=>"JORG",
			"JAUNE/SABLE"=>"JOSA",
			"JAUNE/VIOL/ROUG"=>"JRVI",
			"JAVEL"=>"JVEL",
			"KAKI CLAIR"=>"KACL",
			"KAKI FONCE"=>"KAFO",
			"KAKI"=>"KAKI",
			"KAKI/MANDARINE"=>"KAMA",
			"KAKI MOYEN"=>"KAMO",
			"KAKI/ORANGE"=>"KAOR",
			"KAKI/ROUGE"=>"KARG",
			"KAKI/ROSE"=>"KARS",
			"KAKI/MARINE"=>"KKMA",
			"LAGON/ANIS"=>"LAAN",
			"LAGON"=>"LAGO",
			"LAVANDE"=>"LAVA",
			"LIE DE VIN"=>"LDVI",
			"LICHEN"=>"LICH",
			"LILAS"=>"LILA",
			"LIME"=>"LIME",
			"SABLE CHINE LIN"=>"LIN",
			"LION ROUILLE"=>"LION",
			"LIVORNO VERT"=>"LIVO",
			"MARINE/BLANC"=>"MABL",
			"MAUVE CLAIR"=>"MACL",
			"ECRU/MARINE"=>"MAEC",
			"MARRON FONCE"=>"MAF2",
			"MAUVE FONCE"=>"MAFC",
			"MARRON/FUSHIA"=>"MAFU",
			"GRIS/MARRON"=>"MAGR",
			"MAIS"=>"MAIS",
			"MALABAR"=>"MALA",
			"MARINE/MOUTARDE"=>"MAMO",
			"MULTI_ANIS_CL"=>"MANC",
			"MANDARINE"=>"MAND",
			"MULTI_ANIS_MO"=>"MANM",
			"MAUVE PALE"=>"MAPA",
			"MARRON CLAIR"=>"MARC",
			"MARELLE BEIGE"=>"MARE",
			"MARINE/ROUGE"=>"MARG",
			"MARINE"=>"MARI",
			"MARRON MOYEN"=>"MARM",
			"MARRON"=>"MARO",
			"MARINE/SABLE"=>"MASA",
			"MASTIC"=>"MAST",
			"MAUVE MOYEN"=>"MAUM",
			"MAUVE"=>"MAUV",
			"MARIVERT"=>"MAVE",
			"MARINE/VIOLET"=>"MAVI",
			"MULTI_BEIGE_CL"=>"MBEC",
			"MULTI_BEIGE_FC"=>"MBEF",
			"MULTI_BEIGE_MO"=>"MBEM",
			"MULTI_BLEU_CL"=>"MBLC",
			"MULTI_BLEU_FC"=>"MBLF",
			"MULTI_BLEU_MO"=>"MBLM",
			"MULTI_BORDO_FC"=>"MBOF",
			"MULTI_BRIQUE_FC"=>"MBRF",
			"MULTI_BRIQUE_MO"=>"MBRM",
			"MULTI_CIEL_MO"=>"MCIM",
			"MENTHOL CLAIR"=>"MECL",
			"MENTHOL FONCE"=>"MEFC",
			"MENTHOL MOYEN"=>"MEMO",
			"MENTHOL"=>"MENT",
			"MULTI_GRIS_CL"=>"MGRC",
			"MULTI_GRIS_FC"=>"MGRF",
			"MULTI_GRIS_MO"=>"MGRM",
			"MULTI_JAUNE_CL"=>"MJAC",
			"MULTI_JAUNE_FC"=>"MJAF",
			"MULTI_JAUNE_MO"=>"MJAM",
			"MULTI_KAKI_CL"=>"MKAC",
			"MULTI_KAKI_MO"=>"MKAM",
			"MULTI_KAKI FONC"=>"MKFO",
			"MULTI_MARRON_MO"=>"MMA2",
			"MULTI_MAUVE_CL"=>"MMAC",
			"MULTI_MAUVE_FC"=>"MMAF",
			"MULTI_MAUVE_MO"=>"MMAM",
			"MOKA"=>"MOKA",
			"MULTI_ORANGE_CL"=>"MORC",
			"MULTI_ORANGE_FC"=>"MORF",
			"MULTI_ORANGE_MO"=>"MORM",
			"MOUSSE"=>"MOUS",
			"MOUTARDE"=>"MOUT",
			"MULTI_ROSE_CL"=>"MR03",
			"MULTI_ROSE_FC"=>"MR04",
			"MULTI_ROSE_MO"=>"MRO2",
			"MULTI_ROUGE_CL"=>"MROC",
			"MULTI_ROUGE_FC"=>"MROF",
			"MULTI_ROUGE_MO"=>"MROM",
			"MARRON/SABLE"=>"MRSA",
			"MAUVE/ROSE/VIOL"=>"MRVI",
			"MULTI_TURQUO_CL"=>"MTUC",
			"MULTI_ASSORTIS"=>"MUAS",
			"MULTI_BLANC"=>"MUBL",
			"MULTI_BORDEAUX"=>"MUBO",
			"MULTI_BRIQUE"=>"MUBR",
			"MULTI_ECRU"=>"MUEC",
			"MULTI_FUCHSIA"=>"MUFU",
			"MULTICOLORE"=>"MULT",
			"MULTI_MARINE"=>"MUMA",
			"MULTI_NOIR"=>"MUNO",
			"MURE"=>"MURE",
			"MULTI_TURQUOISE"=>"MUTU",
			"MULTI_VERT_CL"=>"MVEC",
			"MULTI_VERT_FC"=>"MVEF",
			"MULTI_VERT_MO"=>"MVEM",
			"MULTI_VIOLET_FC"=>"MVIF",
			"MULTI_VIOLET_MO"=>"MVIM",
			"MAUVE/ROSE"=>"MVRO",
			"NACRE"=>"NACR",
			"NATUREL/OCEAN"=>"NAOC",
			"NATUREL/ORANGE"=>"NAOR",
			"NATUREL/ROUGE"=>"NARO",
			"NATUREL/TABAC"=>"NATA",
			"NATUREL"=>"NATU",
			"VERT NIL"=>"NIL",
			"BLANC/NOIR"=>"NOBL",
			"MARINE NOCTURNE"=>"NOCT",
			"ECRU/NOIR"=>"NOEC",
			"NOEUDS"=>"NOEU",
			"NOIR"=>"NOIR",
			"NOUGAT"=>"NOUG",
			"NUITS BLEUES"=>"NUIT",
			"ORAN/BLEU/JAUNE"=>"OBJE",
			"OCEAN"=>"OCEA",
			"ORANG/PAIL/ROUG"=>"OPRG",
			"OR"=>"OR",
			"ORANGE"=>"ORAN",
			"ORANGE CLAIR"=>"ORCL",
			"ORANGE FONCE"=>"ORFC",
			"ORANGE FLUO"=>"ORFL",
			"GRIS ORAGE"=>"ORGR",
			"MARRON/ORANGE"=>"ORMA",
			"ORANGE MOYEN"=>"ORMO",
			"OR/ROUGE"=>"OROG",
			"ORANGE PALE"=>"ORPA",
			"ORANGE/ROUGE"=>"ORRG",
			"ORANGE/ROSE"=>"ORRO",
			"ORANGE/VERT"=>"ORVR",
			"SANGUINE"=>"OSAN",
			"OXYDE"=>"OXYD",
			"PAILLE"=>"PAIL",
			"VERT PALMIER"=>"PALM",
			"PAON"=>"PAON",
			"PARME/ORANGE"=>"PAOR",
			"PARME/PISTACHE"=>"PAPI",
			"PARME"=>"PARM",
			"PASTEQUE"=>"PAST",
			"PARME/VERT"=>"PAVE",
			"pis-ci/begr-ci"=>"PCBC",
			"PERLE"=>"PERL",
			"VERT PERROQUET"=>"PERR",
			"PETROLE"=>"PETR",
			"PIERRE"=>"PIER",
			"PISTACHE"=>"PIS",
			"PISTACHE/TABAC"=>"PITA",
			"PISTAC/TURQUOIS"=>"PITU",
			"POMME"=>"POMM",
			"POURPRE"=>"POUR",
			"PRUNE/CIEL"=>"PRCI",
			"PRUNE/ECRU"=>"PREC",
			"PRUNE/PARME"=>"PRPA",
			"PRUNE/ROSE"=>"PRRS",
			"PRUN/SABLECHINE"=>"PRSB",
			"PRUNE"=>"PRUN",
			"RAYE MARINE"=>"RAY6",
			"ROSE BALLERINE"=>"ROBA",
			"BLEU/ROSE"=>"ROBE",
			"ROSE CLAIR"=>"ROC2",
			"ROUGE CHINE"=>"ROCH",
			"ROUGE CLAIR"=>"ROCL",
			"ROSE FONCE"=>"ROF2",
			"ROUGE FONCE"=>"ROFC",
			"ROSE FLUO"=>"ROFL",
			"GRIS/ROSE"=>"ROGR",
			"ROSE INDIEN"=>"ROIN",
			"ROSE MOYEN"=>"ROM2",
			"ROUGE MOYEN"=>"ROMO",
			"RONDA MOUTARDE"=>"ROND",
			"ROSE"=>"ROSE",
			"ROSE PALE"=>"ROSP",
			"ROUGE AURORE"=>"ROUA",
			"ROUGE CHINE"=>"ROUC",
			"ROUGE"=>"ROUG",
			"ROUILLE"=>"ROUI",
			"ROUGE/VERT"=>"ROVE",
			"ROSE/ROUGE"=>"RSRG",
			"BLANC/ROUGE NE PLUS UTILISER"=>"RUBL",
			"ECRU/ROUGE"=>"RUEC",
			"NOIR/ROUGE"=>"RUNO",
			"SABLE"=>"SABL",
			"SABLE/BLEU"=>"SABU",
			"SAFRAN"=>"SAFR",
			"SAFRAN/HERMES"=>"SAHE",
			"SANTA FE CIEL"=>"SANT",
			"SAPHI"=>"SAPH",
			"SAPIN"=>"SAPI",
			"SAUMON"=>"SAUM",
			"SOLEIL"=>"SOLE",
			"SOPHORA BLEU"=>"SOPH",
			"SOURIS"=>"SOUR",
			"SPAHI"=>"SPAH",
			"CAMEL/STONE"=>"STCA",
			"STONE"=>"STON",
			"ROUGE/STONE"=>"STRG",
			"TABAC"=>"TABA",
			"TABAC/ROUILLE"=>"TARL",
			"TABAC/ROUGE"=>"TARO",
			"TABAC/SAUMON"=>"TASA",
			"TABAC/TURQUOISE"=>"TATU",
			"TAUPE"=>"TAUP",
			"TOMETTE"=>"TOME",
			"TOURNESOL"=>"TOUR",
			"TRANSPARENT"=>"TRAN",
			"TURQUOISE CLAIR"=>"TUCL",
			"ECRU/TURQUOISE"=>"TUEC",
			"TURQUOISE FONCE"=>"TUFC",
			"TUILE"=>"TUIL",
			"MARRON/TURQUOIS"=>"TUMA",
			"TURQUOISE MOYEN"=>"TUMO",
			"TURQUOISE"=>"TURQ",
			"TURQUOISE/VERT"=>"TUVE",
			"VALESCURE BLEU"=>"VALE",
			"VANILLE"=>"VANI",
			"VERT DE GRIS"=>"VDGR",
			"BLANC/VERT"=>"VEBL",
			"CIEL/VERT"=>"VECI",
			"VERT CLAIR"=>"VECL",
			"VERT DORE"=>"VEDO",
			"ECRU/VERT"=>"VEEC",
			"VERT FONCE"=>"VEFC",
			"VERT FLUO"=>"VEFL",
			"MARINE/VERT"=>"VEMA",
			"VERT MOYEN"=>"VEMO",
			"VERT PALE"=>"VEPA",
			"VERT D'EAU"=>"VERD",
			"VERMILLON"=>"VERM",
			"ROSE/VERT"=>"VERO",
			"VERT"=>"VERT",
			"BLANC/VIOLET"=>"VIBL",
			"VIOLET CLAIRE"=>"VICL",
			"ECRU/VIOLET"=>"VIEC",
			"VIOLET MOYEN"=>"VIMO",
			"NOIR/VIOLET"=>"VINO",
			"VIOLET FONCE"=>"VIOF",
			"VIOLET"=>"VIOL",
			"ROSE/VIOLET"=>"VIRO",
			"VISON"=>"VISO",
			"VERT/VIOLET"=>"VRVI",
			"VIEUX ROSE"=>"VXRO");


        $colorCode= false;

        if($reverse) {

            if(in_array($color, $codeColors, true)){
                $colorCode = $color;//array_search($color, $codeColors, true);
            }
        } else {
             if (isset($codeColors[$color])) {
                $colorCode= $codeColors[$color];
             }
        }
        return $colorCode;
	}