<?php
/**
 *
 * @author tbondois
 */
class EaiFormatterCsvSequenced extends EaiFormatterCsv
{
	
// 	protected $tmpLine = array();

	protected $sequence;
	
	

	public function _getElementFromRaw($rawData)
	{
		
		$element = array();
		
		//gestion sequence
		
		if (isset($this->sequence) && $this->sequence !== '') {
			
			$sequences = array_flip(explode(',', $this->sequence));
			
			$previous = $current = false;
			
			do {

				$rawData = $this->connector->getRawData();
					
				$tmpElement = $this->format($rawData);
					
				$current = trim($tmpElement[0]);
					
				if (!isset($sequences[$current])) {
					$this->log("Sequence not defined : $current", 'warn');
					continue;
				}

				if ($previous !== false && $sequences[$previous] >  $sequences[$current] ) {
					//dump("break on ".$tmpElement[1], $previous, $current);
					$this->connector->reserveLastData();
					
					break;
						
				} else {
					//dump("no break on ".$tmpElement[1], $previous, $current, $sequences[$previous], $sequences[$current] );
						
					if (!empty($tmpElement)) {
						$element[$current][] = $tmpElement;
					}
					
				}
					
				$previous = $current;
				
			} while ($this->connector->fetchRawData());
			
		} else {
			//pas de groupage par sequence
			$element = $this->format($rawData);;//on utilise comme clés l'index de colonne
		}
		
		$this->methodFinish();
		return $element;
	}
	
// 	public function flushTemp()
// 	{
// // 		dump('flushTemp', $this->tmpLine);
// 		$this->tmpLine = array();
// 	}
	


}//class