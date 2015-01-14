<?php
/**
 *
 * @author tbondois
 */
class EaiFormatterCsv extends EaiFormatter
{
	protected $encoding  = "utf-8";

	protected $separator = ';';

	protected $headline  = 1;

    /**
     * Pour gérer l'écriture de saut de ligne
     * @var string
     */
    protected $eol       = "\r\n";

	protected $enclosure = '';

	protected $indexes   = array();

	public function _getElementFromRaw($rawData)
	{
		$element = array();

		if ($this->connector->getProp('csv')) {
		  $values = $rawData; // Déjà un tableau de données CSV
		} else {
		  $values = $this->format($rawData);
		}

		if ($this->elementsFormatted+1 == $this->headline && empty($this->indexes)) {
			//elements formatted commence a 0, headline a 1 (si 0 = pas de headline). headline defini les clés d'index du tableau $element, utilisées pour le mapping

			$this->indexes = $values;
			if (count($this->indexes)<=1) {
				$this->log("determining headline : ".implode(' | ', $this->indexes)." , ligne ".$this->elementsFormatted, 'warn');
			} else {
				$this->log("determining headline : ".implode(' | ', $this->indexes)." , ligne ".$this->elementsFormatted);
			}
		} else {
			//on ignore la ligne titre et les précédentes

			//$values = str_getcsv($rawData, $this->getProp('separator'), $this->getProp('enclosure'));

			if (!empty($this->indexes)) {
			    if (count($this->indexes)==count($values)) {
    				$element = array_combine($this->indexes, $values);
			    } else {
			      $element = array();
			      foreach ($values as $value) {
			        $samples[] = preg_replace('/\n|\r/m', '', substr(strip_tags($value), 0, 20));
			      }
			      $this->log('Newline or incorrect column number (headers: '.count($this->indexes).' - values: '.count($values).') in ('.implode(' - ', $samples).')', 'err');
			    }
			} else {
				$element= $values;
			}
		}


		//dump("***_eaiFetchElement:", $this->element);
 		//$this->incrementElementsFormated();

		//$this->methodFinish();
		//dump($this->elementsFormatted, $element, $this->indexes);
		return $element;
	}


	protected function format($rawData)
	{
		$r = str_getcsv($rawData, $this->getProp('separator'), $this->getProp('enclosure'));
		return $r;
	}

	public function _getRawFromElement($element, $empty_buffer= false)
	{

		$rawData = $this->getFlatLine($element);
		//dump($element, $rawData);
		//$this->incrementElementsFormated();
		return $rawData;
	}


	public function getFlatLine($element)
	{

		$rawData = '';
	    if (empty($element) or !is_array($element)) {
	    	$this->log("getFlatLine begin : assumed EOF, empty element given at element n°".$this->elementsFormatted, 'debug');

	    } else {
            if (empty($this->indexes) && $this->elementsFormatted+1 == $this->headline) {

              //Ecriture des entêtes
              foreach ($element as $key => $val) {
                  if (is_array($val) or is_object($val)) {
                  unset($element[$key]);
                          $this->log($key.': element type (array or object) not compatible for this formatter', 'warn');
                  }
              }
              $this->indexes = array_combine(array_keys($element), array_keys($element));
              $rawData = implode($this->getProp('separator'), $this->indexes).$this->getProp('eol');
              $element = array_intersect_key($element, $this->indexes);

            }
            //dump($element);
            $rawData.= implode($this->getProp('separator'), $element);
            $rawData.= $this->getProp('eol');
            //dump($rawData);

    		if (empty($rawData)) {
    			$this->log("getFlatLine end : empty rawData but not empty element. EOF assumed at element n°".$this->elementsFormatted);
    		}
	    }
	    return $rawData;
	}
}//class