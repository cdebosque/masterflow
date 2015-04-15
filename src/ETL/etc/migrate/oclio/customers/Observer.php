<?php


debug_print_backtrace();
exit;


class Observer extends EaiObject
{

	public function onMapIn($eaiData, $way = null)
	{
		if(isset($eaiData['customers_dob'])){
			$eaiData['customers_dob'] = dateday();
		}

		$eaiData= array($eaiData);

		return $eaiData;
	}

}