<?php
/**
 * 
 * @package eai-generic
 * 
 * @author VP
 */
class EaiEvent extends EaiObject
{
    private $args;

    private $obj;
    
    private $objClass;

    function setArg($key, $val)
    {
        $this->args[$key]= $val;
    }

    function getArg($key)
    {
        return $this->args[$key];
    }
    
     function getObj()
    {
        return $this->obj;
    }
    
    function setObj($obj)
    {
        $this->obj= $obj;
    }
    
    
    function setObjClass($value)
    {
        $this->objClass = $value;
    }


    function getObjClass()
    {
        return $this->objClass;
    }

    //$store = $observer->getEvent()->getStore();
}