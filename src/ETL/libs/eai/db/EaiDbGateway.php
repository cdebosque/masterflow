<?php
/**
 * 
 * @uses ext/Zend classes
 *
 * @package eai-generic
 * 
 * @author abobin
 *
 */

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Expression;

class EaiDbGateway extends EaiObject
{
  /** @var Zend\Db\Adapter\Adapter */
  public $adapter;
	
  /**
   * Initialise la connexion à la base de données.
   */
  public function __construct($config = array())
  {
  	if(empty($config))
  	{	
	    $config = array(
	      'driver' => 'mysqli',
	      'database' => DB_BASE,
	      'username' => DB_USER,
	      'password' => DB_PASS,
	    );
	    if (defined('DB_HOST')) {
	      $config['hostname'] = DB_HOST;
	    }
	    if (defined('DB_PORT')) {
	      $config['port'] = DB_PORT;
	    }
  	}
  	  
    $adapter = new Adapter($config);
    $this->adapter = $adapter;
  }

  /**
   * @return Zend\Db\Adapter\Adapter
   */
  public function getAdapter()
  {
    return $this->adapter;
  }
  
  /**
   * Renvoie un objet TableGateway depuis un nom de table donné. 
   * 
   * @param string $name
   * @return \Zend\Db\TableGateway\TableGateway
   */
  public function getTable($name)
  {
    $table = new TableGateway($name, $this->adapter);
    return $table;
  }

  /**
   * Convertit une chaîne de caractères en Expression SQL
   * 
   * @param string $expression
   * @rturn Zend\Db\Sql\Predicate\Expression
   */
  public function exp($expression)
  {
    $exp = new Expression($expression);
    return $exp;
  }

}