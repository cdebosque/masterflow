<?php
/**
 * @todo ALL
 * @author jaymard
 */
class EaiFormatterFix extends EaiFormatter
{
	protected $encoding  = "utf-8";

	protected $fields = array();
	protected $field = array();

	
	public function _getRawFromElement($element, $empty_buffer = false)
	{
		return $element;
	}

	/**
	 * @see EaiFormatter::fetchElement()
	 *
	 * @param EaiConnector $connector
	 */
	public function _getElementFromRaw($rawdata)
	{
		$this->methodStart();

    $this->rawData = $rawdata;
		$this->element = false;

    // Vérification de la configuration des champs
    if (empty($this->fields)) {
      $this->log("Fix fields configuration file needed", 'error');
    }

    //dump($rawdata, $this->field, $this->fields);

    exit("exit dans FormatterFix fetchElement");
			
			/*//
			if ($this->elementsFetched+1 == $this->headline) {
				$this->element = array();
				$this->keys = str_getcsv($this->rawData, $this->getProp('separator'), $this->getProp('enclosure'));
				if (count($this->keys)<=1)
				    $this->log("determining headline '| ".implode(' | ', $this->keys)." |'", 'warn');
			    else
				    $this->log("determining headline '| ".implode(' | ', $this->keys)." |'");
// 				$this->rawData = $connector->fetchRawData();
			}
			else
			{
				//on ignore la ligne titre et les précédentes
				$values = str_getcsv($this->rawData, $this->getProp('separator'), $this->getProp('enclosure'));
				if(!empty($this->keys)){

				    $elements= array_combine($this->keys, $values);

// 				    if ($elements===false and !empty($values))
// 				        throw new Exception('array_combine');

					$this->element = array_combine($this->keys, $values);
				} else {
					$this->element = $values;
				}
// 				dump($this->element);
 			}
			$this->elementsFetched++;



		} else {
			$this->element = false;
		}*/

		$this->methodFinish();
		return $this->element;
	}


	public function getRawFromElement()
	{
		$this->methodStart();
		$rawData = implode($this->getSeparator(), $this->element)."\r\n";
		$this->methodFinish();
		return $rawData;
	}

}//class