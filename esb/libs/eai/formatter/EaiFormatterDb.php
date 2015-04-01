<?php
/**
 *
 * @author cdebosque
 *
 */
class EaiFormatterDb extends EaiFormatter
{

	/**
	 * @see EaiFormatter::fetchElement()
	 */
	public function _getElementFromRaw($rawData)
	{
		//WriteTruc ("Formatter Screen : _getElementFromRaw");
		return $rawData;
	}

	/**
	 * @see EaiFormatter::getRawFromElement()
	 */
	public function _getRawFromElement($element, $empty_buffer= false)
	{

		
		return $element;
	}

	public function _eaiWriteLine($element)
	{
		
	}
}//class