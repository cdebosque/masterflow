<?php

/** 
 * 
 * @package eai-generic
 * 
 * @author tbondois
 */
class EaiDebug 
{
	/**
	 * @return void
	 */
	public static function printBacktrace()
	{
		$traces = debug_backtrace();
		foreach ($traces as $trace) {
			echo "Backtrace:". $trace['file']." ".$trace["line"].PHP_EOL;
		}
	}
	
	public static function getFunctions()
	{
		$traces = debug_backtrace();
		$r = "";
		foreach ($traces as $trace) {
	
			if (isset($trace['function']) && !in_array($trace['function'], array(__METHOD__, "dump", "methodStart", "methodFinish", "getCalledFunction", "getFunctionsTrace", "getFunctions")) ) {
				if(isset($trace['class'])){
					$class = $trace['class'].".";
				} else {
					$class = '';
				}
				$echoArgs = array();
				if(count($trace['args'])){
					foreach($trace['args'] as $arg){
						$echoArgs [] = self::getDisplayValue($arg);
					}
				}
				$r[] =  $class.$trace['function']."(".implode(', ', $echoArgs).")";
			}
		}
		return $r;
	}
	
	public static function getFunctionsTrace()
	{
		$r = '';
		$funcs = self::getFunctions();
		if(!empty($funcs)){
			$r = implode(" > ", array_reverse($funcs));
		}
		return $r;
	}
	
	public static function getCalledFunction($level=0)
	{
		$r = '';
		$funcs = self::getFunctions();
		if(isset($funcs[$level])){
			$r = $funcs[$level];
		}
		return $r;
	}
	
	public static function getDisplayValue($arg)
	{
		if(is_string($arg)) {
			$r = "'".substr($arg, 0, 99)."'";
		} elseif (is_bool($arg)) {
			$r = "[bool]".($arg ? 'True' : 'False');
		} elseif (is_scalar($arg)) {
			$r = "[".gettype($arg). "]".$arg;
		} elseif(is_array($arg)) {
			
			$r = count($arg)."-rows ";
			
			if (count($arg) > 0 && count($arg, COUNT_RECURSIVE) <= 5) {
				//$r.= str_replace(array(PHP_EOL, "\r", "\n", "  "), '', lcfirst(var_export($arg, true)));
			} else {
				$r.= gettype($arg);
			}
		} else {
			$r = gettype($arg);
		}
		return $r;
	}
	
}//end class





