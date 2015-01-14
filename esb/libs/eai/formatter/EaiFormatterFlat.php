<?php
/**
 * @todo all
 * @author 
 *
 */
class EaiFormatterFlat extends EaiFormatter
{
	public function _getElementFromRaw($rawData)
	{
		return $rawData;
	}
	
	public function _getRawFromElement($element, $empty_buffer = false)
	{
		return $element;		
	}
	
}//class
?>