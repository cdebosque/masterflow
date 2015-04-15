<?php
/**
 *
 * @author tbondois
 *
 */
class EaiFormatterXml extends EaiFormatterArray
{
	protected $encoding       = "UTF-8";

	protected $version        = "1.0";

	protected $allElements    = null;

	protected $xmlNodeRoot    = 'elements';

	protected $xmlNodeElement = 'element';

	public function _getElementFromRaw($rawData)
	{
		return $rawData;
	}
	
	public function _getRawFromElement($element, $empty_buffer= false)
	{

		if ($empty_buffer ) {
		  if (!is_null($this->getXmlNodeRoot())) {
		  	$rawData= "\n</".$this->getXmlNodeRoot().">";
		  }
		} else {

    		if (empty($this->elementsFormatted)) {
        		$rawData = '<?xml version="'.$this->getVersion().'" encoding="'.$this->getEncoding().'"?'.'>';
        		if (!is_null($this->getXmlNodeRoot())) {
        			$rawData.= "\n<".$this->getXmlNodeRoot().">";
        		}
    		} else {
    		    $rawData = '';
    		}

    		/*if (!is_null($this->getXmlNodeElement())) $rawData .= "\n<".$this->getXmlNodeElement().">";
    		$rawData .= $this->xmlFromArray($this->element, '', 1);
    		if (!is_null($this->getXmlNodeElement()))	$rawData .= "\n</".$this->getXmlNodeElement().">";*/

    		$rawData.= $this->xmlFromArray($element, $this->xmlNodeElement);
    		
//     		$this->incrementElementsFormated();
		}

		return $rawData;
	}




	public function xmlFromArray($data, $nodeName = 'elements')
	{
		//dump("xmlFromArray", $data, $nodeName );
		try {
    		$xml = Array2XML::createXML($nodeName, $data);
    		$r = $xml->saveXML();

    		$r = preg_replace('/<\?xml.*?\?>\s*/', null, $r); //on génère ici des morceaux de XML : on enlève l'entete que rajo

		} catch (Exception $e) {
		    self::log('xmlFromArray: '.$e->getMessage(), 'err');
		    $r = '';
		}
		return $r;

	}

}//class