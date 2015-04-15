<?php
/**
 *
 * @package eai-generic
 *
 * @author tbondois
 *
 */
class EaiHandler extends EaiObject
{
	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var string 'import'|'export'
	 */
	protected $type;

	/**
	 * @var array
	 */
	//protected $eaiDatas = array();

	/**
	 * @var EaiDriver
	 */
	protected $driverIn;

	/**
	 * @var EaiDriver
	 */
	protected $driverOut;

	/**
	 * @var EaiConfiguration
	 */
	protected $config;

	/**
	 * @var string
	 */
	protected $config_key = 'interface';

    /**
     * Indique on est en train de lire le premier "lot" de données
     * @var bool
     */
    public $isFirst = false;

    /**
     * Indique on est en train de lire le dernier "lot" de données.
     * Attention il est a true si le dernier enregistrement est passé, et non en cours.
     * TODO fixer ca
     * @var bool
     */
    public $isLast = false;

    /**
     * Mode Verroulage activé ou non
     *
     * @var bool
     */
    protected $lockMode = false;

    /**
     * Limite de temps d'exécution de l'interface, en minutes. ex: 360 = 6h
     * @var int
     */
    protected $timeout;

    /**
     * Limite mémoire en Mo. Ex : memory_limit="4096" dans interface.xml donnera $this->memory_limit="4096M";
     * @var mixed
     */
    protected $memoryLimit;

	/**
	 * @param string $identifier
	 */
	public function __construct($identifier)
	{
		parent::__construct();	//le constructeur doit etre avant methodStart() sinon pb avec EaiLogger->log
		$this->methodStart();

		$this->identifier   = $identifier;

		$this->type = $this->config()->getAttribute('type');
		if ($this->type===false) {
		    exit;
		}

		//$this->log('eai instanciation for identifier:'.$this->identifier, 'info');

		$this->methodFinish();
	}

	public function __destruct()
	{
		$this->methodStart();

		parent::__destruct();

		$this->methodFinish();
	}

	public function run()
	{
		$this->methodStart();

		$r = false;

		try {

            if ($this->open()) {

				while ($this->read()) {

                    if ($this->write()) {

						$r = true;

					}//write

				}//read

				$this->write(true);

				$this->close();
			}//open

		} catch (Exception $e) {
			$this->log("Exception : {$e->getMessage()}", 'err');
		}

		$this->methodFinish($r);
		return $r;
	}//function run()


	/**
	 *
	 * @return boolean
	 */
	public function open()
	{
		$this->methodStart();

        $this->logStart();

		$r = false;

        $lockMode = $this->config()->getAttribute("lock");
        $this->lockMode = (bool)$lockMode;

        $timeout = $this->config()->getAttribute("timeout");
        $this->timeout = (int)$timeout;//en minutes
        set_time_limit($this->timeout * 60);//en secondes
        $memoryLimit = $this->config()->getAttribute("memoryLimit");
        if (is_numeric($memoryLimit)) {
            $this->memoryLimit = (int)$memoryLimit."M";
            ini_set("memory_limit", $this->memoryLimit);
            $this->log("memoryLimit set at ".$this->memoryLimit);
        }

        //$this->dbRow->getDb()->getTable('Dataflow');

        $filename = str_replace(Esb::ROOT, '', $this->logger->getLogFilename());
        $this->dbRow->updateLogFilename($filename);

        $this->registerObservers();

    $this->driverIn  = new EaiDriver(Esb::WAY_IN , $this->config());
    $this->driverOut = new EaiDriver(Esb::WAY_OUT, $this->config());

		// @TODO : Ajouter un setter/getter et protéger la variable.
		//$this->driverIn->dbRow = $this->dbRow;
		//$this->driverOut->dbRow = $this->dbRow;

		if ($this->driverIn->open()) {
				if ($this->driverOut->open()) {
					$r = true;
				} else $this->log('opening driverOut', 'err');
		} else $this->log('opening driverIn', 'err');

		$this->methodFinish(__METHOD__);
		return $r;
	}//function open()


	public function close($error= false)
	{
		$this->methodStart();
		if (!empty($this->driverOut)) {
		    $out = $this->driverOut->close($error);
		} else {
		    $out = true;
		}
		if (!empty($this->driverIn)) {
		    $in = $this->driverIn->close($error);
		} else {
		    $in = true;
		}

		$r = ($in and $out);

		$this->logFinish($error);

		$this->methodFinish($r);
		return $r;
	}

	/**
	 * @return boolean
	 */
	public function read()
	{
		$this->methodStart();

		$eaiDatas = $this->driverIn->fetchEaiDatas();

		//$eaiDatas = $this->driverIn->getEaiDatas();
		if ($eaiDatas) {
			$r = true;
		} else {
			$r = false;
		}
		//dump('HANDLER READ EAIDATAS', $eaiDatas);
		$this->log("Read ".count($eaiDatas)." eaiDatas");
		$this->isFirst = $this->driverIn->isFirst;
		$this->isLast  = $this->driverIn->isLast;

		$this->methodFinish($r);
		return $r;
	}

	/**
	 * Appel au driver d'écriture
	 *
	 * @param bool $empty_buffer: Flag indiquant que l'on est sur le dernier appel à cette méthode
	 *
	 * @return boolean
	 */
	public function write($empty_buffer = false)
	{
		$this->methodStart();
        $written = false;//variable de de retour
		$attempts = $success = 0; //compteurs
		$losses = array();//contiendra les eaiDatas qui n'ont pas bien été écrites

		$eaiDatas = $this->driverIn->getEaiDatas();

        $this->driverOut->isFirst = $this->isFirst;
        $this->driverOut->isLast  = $this->isLast;


 		if ($eaiDatas || $empty_buffer) {

		    $this->driverOut->setEaiDatas($eaiDatas);

		    $attempts++;
			$written = $this->driverOut->putEaiDatas();
			if ($written) {
				$success++;
			} else {
				$losses[] = $eaiDatas;
			}
			//dump("writing", $written, $eaiDatas);

			if ($empty_buffer) {
                $attempts++;
			    $written = $this->driverOut->putEaiDatas($empty_buffer);
                if ($written) {
                    $success++;
                } else {
                    $losses[] = $eaiDatas;
                }
			}

		}

		$this->methodFinish();
		if($attempts == $success) {
			$this->log("Write ".count($eaiDatas)." eaiDatas, attempts: $attempts ,success: $success (ALL)");
		} elseif(!$this->isLast) {
			$this->log("Write ".count($eaiDatas)." eaiDatas, attempts: $attempts, success: $success (LOSSES or data refactoring by observers).", 'warn');
			//dump("Except if you refactor data with observers, there is losses in:", $losses);
		}
		//dump($eaiDatas);
		return $written;
	}


	public function registerObservers()
	{
		$this->methodStart();
		$r = true;
		$classes = $this->config()->getArray('observers/observer');
		if (!empty($classes)) {

			foreach ($classes as $class) {
			  $classFilename= Esb::ETC."{$this->identifier}/$class.php";

				if (file_exists($classFilename)) {

					include_once $classFilename;//pour les Observer qui sont pas dans le répertoire autoloadé
                    $parts = explode('/', $class);
                    $className = end($parts);
					Esb::registerObserver($className);
					$this->log("register observer class '$className' located in '{$classFilename}'", 'debug');

				} else {
					if (class_exists($class) or Esb::autoloader($class)) {
						Esb::registerObserver($class);
						$this->log("register standard observer $class.php");
					} else {
						$r = false;
						$this->log("Not exist observer in '{$classFilename}' or eai-autoloaded directory", 'fatal');
					}

				}
			}
		}
		$this->methodFinish($r);
		return $r;
	}


	protected function logStart()
	{
		$this->log("Run in env ".Esb::ENV." : begin at ".date('r'));
		$this->dbRow->updateStart();
	}


	protected function logFinish($error)
	{
		if ($error) {
			$this->log("Run in env ".Esb::ENV." : finish BADLY at ".date('r').". Slap yourself and look what you've done.");
		}
		else {
			$this->log("Run in env ".Esb::ENV." : finish PROPERLY at ".date('r').". Congratulate yourself and look at the report above.");
		}
		$this->dbRow->updateFinish();
	}

}//class