<?php
/**
 *
 * @author cdebosque
 *
 */
class EaiFormatterScreen extends EaiFormatter
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
		//WriteTruc ("Formatter Screen : _getRawFromElement");
		// 		$this->incrementElementsFormated();
		$this->_eaiWriteLine($element);
//		dump($element);
		return $element;
	}

	public function _eaiWriteLine($element)
	{
		echo '
				<div class="tb-left-cell">Line</div>
<div class="tb-right-cell">31.40%</div>
<div class="tb-right-cell">22.33%</div>
<div class="tb-right-cell">35.58%</div>
<div class="tb-right-cell">7.74%</div>
				';
	}
}//class