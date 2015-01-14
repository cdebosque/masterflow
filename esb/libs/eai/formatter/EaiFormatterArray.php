<?php
/**
 *
 * @author tbondois
 *
 */
class EaiFormatterArray extends EaiFormatter
{

	/**
	 * @see EaiFormatter::fetchElement()
	 */
	public function _getElementFromRaw($rawData)
	{
		return $rawData;
	}

	/**
	 * @see EaiFormatter::getRawFromElement()
	 */
	public function _getRawFromElement($element, $empty_buffer= false)
	{
// 		$this->incrementElementsFormated();
		return $element;
	}


}//class