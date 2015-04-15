<?php
class Observer extends EaiObserverProduct
{

	/**
	 * On transforme un arbre de catégories en x elements de catégories, puis en renvoie l'élement actuel.
	 * @param EaiEvent $event
	 */
	public function onSoapCall(EaiEvent $event)
	{
		$connector = $event->getObj();

		$results = $connector->getResults();
		$results = array_values(self::convertTreeToFlatArray($results));// array_values pour avoir les clés bien ordonnées, pour getRawDatasFetched. Sinon la fonction renvoie l'id comme clé de tableau.

		$connector->setResults($results);
	}

	public function onGetRawFromElementsOutFinish(EaiEvent $event)
	{
	    $formatter= $event->getObj();

	    $element= $formatter->getElements();

		if( !empty($element) ) {
// 			$element['properties']=array();
// 			foreach($element as $elKey=>$elVal) {
// 				if (is_object($elVal) ) {
// 					$elVal->node= 'property';
// 					$element['properties'][]= $elVal;
// 					unset($element[$elKey]);
// 				}
// 			}

// 		    dump($element);

			$id = $element['id']; //$element['category_id'];

			$skel = array(
					'@attributes'  => array(
							'id' => $id,
							'xml:lang' => "fr",
					),
					'name' => $element['name'],
					'url' => array('@cdata' => '/'.ltrim($element['url'],'/')),//$element['full_path_name'],
					'broader' => array(
							'@attributes' => array(
									'idref' => $element['idref'] //$element['parent_id'];
									),
							),
					//'productsCount' => null
					);

			$element = $skel;

			if ($formatter->getElementsFormatted()==1) {
				$custRawData = '<?xml version="1.0" encoding="UTF-8"?>
<categories xmlns="http://ref.antidot.net/store/afs#">
	<header>
		<owner>OCP</owner>
		<feed>category</feed>
		<generated_at>'.date('c').'</generated_at>
	</header>'.PHP_EOL;
			} else {
				$custRawData= '';
			}
			//dump($custRawData, $formatter->getElementsFormatted(), $formatter);

			//$xmlRoot = new SimpleXMLElement('<category id="'.$id.'" xml:lang="fr"></category>', LIBXML_NOXMLDECL);

			//$custRawData.= $xmlRoot->asXML();
			//self::arrayToXml($element,$xmlRoot);


			$custRawData.= $formatter->xmlFromArray($element, 'category');
			//TODO: Devrait être pris en compte par LIBXML_NOXMLDECL
		} else {
			$custRawData= '</categories>';
			///if ($formatter->getElementsFormatted() > 1) {
			//	$custRawData = preg_replace('/<\?xml.*?\?**********>\s*/', null, $custRawData);
			//}
		}

		$formatter->connector->setRawData($custRawData);
	}


	function arrayToXml($category_info, &$xmlRoot, $is_utf8=false) {
		foreach($category_info as $key => $value) {
			if(is_array($value)) {

				if ( isset($value['@attributes']) ) {

					if(isset($value['@node']))
							$childKey= $value['@node'];
					else
						  $childKey= $key;

					$child= $xmlRoot->addChild($childKey);
					foreach ($value['@attributes'] as $propKey=>$propVal)
					{
						$child->addAttribute($propKey,$propVal);
					}

				} elseif(!is_numeric($key)){
					$subnode = $xmlRoot->addChild("$key");
					self::arrayToXml($value, $subnode);
				}
				else{
					self::arrayToXml($value, $xmlRoot);
				}
			}
			else {
				if(is_numeric($key)){
					//dump($key, $value, $xmlRoot);
				}


// 				if ( is_object($value) ) {
// 					$child= $xmlRoot->addChild($value->node);
// 					unset($value->node);
// 					foreach (get_object_vars($value) as $propKey=>$propVal)
// 					{
// 						$child->addAttribute($propKey,$propVal);
// 					}

// 				} else
					if ( is_numeric($value) or empty($value) ) {
					$child= $xmlRoot->addChild("$key","$value");
				}
				else
				{
					$child= $xmlRoot->addChild("$key");
					self::addCData($child, $value, $is_utf8);
				}
			}
		}
	}


	/**
	 * @url http://www.php.net/manual/en/simplexmlelement.addchild.php#104458
	 *
	 * @param unknown_type $cdata_text
	 */
	function addCData($obj, $cdata_text, $is_utf8=false)
	{
		if( strpos($cdata_text,'Confort') !==false )
		{
			//$cdata_text= utf8_encode(utf8_decode($cdata_text));
			//var_dump($cdata_text);
		}


		//     if( !$is_utf8 )
		//         $cdata_text= utf8_encode($cdata_text);

		//     $cdata_text= utf8_decode($cdata_text);
		// var_dump($cdata_text);

		$node= dom_import_simplexml($obj);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}



}