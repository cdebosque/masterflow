<?php
/**
 *
 * @todo all
 * @author 
 *
 */
class EaiFormatterJson extends EaiFormatter
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