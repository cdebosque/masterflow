<?php
/**
 * @uses ext/log4php classes
 *
 * @package eai-generic
 *
 * @author tbondois
 *
 */
class EaiLogger
{

	const TRACE   = 'trace';
	const DEBUG   = 'debug';
	const INFO    = 'info' ;
	const WARNING = 'warn' ;
	const ERROR   = 'err'  ;
	const FATAL   = 'fatal';

	/** @var string */
	protected $name;

	/** @var string */
	protected $identifier;

	/** @var Logger */
	protected $log;

	public static $specific = 0;

	public function __construct($name = '')
	{
        $this->identifier = Esb::registry('identifier');
        $this->name = $name;

        if ($this->identifier) {

		    $this->configure();

		    $this->log  = Logger::getLogger($this->name);//appel a une classe log4php
        }
	}

	public function configure()
	{
		if (!Esb::registry("loggerInitialized") ) {

            //on chercher si un fichier de configuration de logging est présent dans le répertoire de l'interface
			$configFilePath = Esb::ETC."{$this->identifier}/logging.php";
			if (!file_exists($configFilePath)) {
				//Si pas de logging activé ou fichier non existant dans dossier cible, on prend le fichier de base
				$configFilePath = Esb::ETC."core/base/logging/".Esb::LOGGING_BASE.".php";
			}
			$arrayConfig = include($configFilePath);

			if (!empty($arrayConfig)) {
				//dump('$_SESSION', $_SESSION);
				Logger::configure($arrayConfig);
				//dump('$arrayConfig', $arrayConfig);
				Esb::register('loggerInitialized', 1);
			}
		}

	}

	/**
	 * Peut être appelée en static ou sur un objet instancé
	 *
	 * @param string $message
	 * @param string $level
	 * @param strinf $name for static call only : name of the logger
	 */
	public function log($message, $level = self::INFO, $name = '')
	{
        if (!$this->identifier) {
            return;
        } elseif (!isset($this)) {
			//en cas d'appel statique
			$logger = new EaiLogger($name);
			$logger->log($message, $level);
			$logger = null;
		} else {
			$message = (string)$message;
			switch (trim(strtolower($level))) {
				case self::TRACE:
					$this->trace($message);
					break;
				case self::DEBUG:
					$this->debug($message);
					break;
				case self::WARNING:
                case 'warning':
					$this->warning($message);
					break;
				case self::ERROR:
				case 'error':
					$this->error($message);
					break;
				case self::FATAL:
					$this->fatal($message);
					break;
				case self::INFO:
				default:
					$this->info($message);
			}

		}
	}

	protected function trace($message)
	{
		$this->log->trace($message);
	}

	protected function debug($message)
	{
		$this->log->debug($message);
	}

	protected function info($message)
	{
		$this->log->info($message);
	}

	protected function warning($message)
	{
		$this->log->warn($message);
	}

	protected function error($message)
	{
		$this->log->error("$message");
	}

	protected function fatal($message)
	{
		$this->log->fatal("$message");
	}

	public function getLogFilename()
	{
		return $this->log->getParent()->getAppender('file')->getFile();
	}

}//class