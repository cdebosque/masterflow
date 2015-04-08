<?php
/**
 *
 * @author cdebosque
 *
 */

// use Zend\Db\Sql\Expression;

class EaiFormatterDb extends EaiFormatter
{
	public $db;
	public $table;
	public function __construct()
	{

		
	}

	/**
	 * @see EaiFormatter::fetchElement()
	 */
	public function _getElementFromRaw($rawData)
	{
// 		echo "<h2>_getElementFromRaw</h2>";
// 		var_dump($rawData);
		//WriteTruc ("Formatter Screen : _getElementFromRaw");
		return $rawData;
	}

	/**
	 * @see EaiFormatter::getRawFromElement()
	 */
	public function _getRawFromElement($element, $empty_buffer= false)
	{
// 		echo "<h2>_getRawFromElement</h2>";
// 			var_dump($element);
		
		return $element;
	}

	public function _eaiWriteLine($element)
	{
		
	}
}//class