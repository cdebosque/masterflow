<?php
/**
 *
 * @package eai-generic
 *
 * @author tbondois
 *
 */
class EaiDriver extends EaiObject
{

    /**
     * @var string 'import'|'export'
     */
    protected $interfaceType;

    /**
     * @var string 'in'|'out'
     */
    protected $way;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * Nombre de ligne a lire/ecrire
     * @var int
     */
    public $fetching = 0;

    public $mode;

    /**
     * @var boolean
     */
    public $fetchingEnded = false;

    /**
     * @var array - used for reading
     */
    public $eaiDatas = array();

    /**
     * @var array    - used for writing
     */
// 	protected $elements        = array();

    /**
     * @var EaiConnector
     */
    //protected $connector;

    /**
     * @var EaiFormatter
     */
    public $formatter;

    /**
     * @var EaiMapper
     */
    public $mapper;

    /**
     * Indique on est en train de lire le premier "lot" de données
     * @var bool
     */
    public $isFirst = false;

    /**
     * Indique on est en train de lire le dernier "lot" de données
     * @var bool
     */
    public $isLast = false;


    /**
     * @param string $way in|out
     * @param EaiConfiguration $config
     */
    public function __construct($way, EaiConfiguration $config)
    {
        parent::__construct();
        $this->methodStart();

        //Sens de l'interface in/out
        $this->way = $way;

        $this->config = $config;

        //Type d'interface import ou export
        $this->interfaceType = $config->getAttribute('type');

        //Instanciation du formatter
        $this->initFormatter();


        //Identifiant unique du driver
        $this->identifier = $this->interfaceType . '-' . $this->way . '-' . $config->getAttribute('type', "$way/connection") . '-' . $config->getAttribute('type', "$way/format") . '-';

        $this->fetching = (int)$config->getAttribute('fetching', $way);
        if ($this->fetching < 0) {
            $this->fetching = 0;
        }

        if ($way == Esb::WAY_OUT) {
            $this->mode = $config->getAttribute('mode', $way);
            if ($this->mode != 'block') {
                $this->mode = 'line';
            }
        }

        if (($config->getAttribute('active', "$way/mapping") === 'auto'
            && (($this->way == Esb::WAY_IN && stripos($config->getAttribute('type'), 'import') !== false)
                || ($this->way == Esb::WAY_OUT && stripos($config->getAttribute('type'), 'export') !== false)
            ))
            || ($config->getAttribute('active', "$way/mapping") === '1')
        ) {
            $mappingIdentifier = $config->getIdentifier();
            $this->identifier .= "withmap";
        } else {
            $mappingIdentifier = null;
            $this->identifier .= "nomap";
        }
        $this->log("driver identifier : {$this->identifier}, mapping identifier : $mappingIdentifier", 'debug');
        $this->mapper = new EaiMapper($mappingIdentifier, $this->way, $config);
    }

    protected function initConnector()
    {
        $configPath = $this->way . "/connection";
        if ($this->config()->hasAttribute('type', $configPath)) {
            $this->formatter->connector = EaiConnector::factory($this->config->getAttribute('type', $configPath)
                , $this->config->getArray($configPath)
                , $this->way);
        } else {
            $this->fault(self::getWay() . "/format : type not defined");
        }
    }

    protected function initFormatter()
    {
        $configPath = $this->way . "/format";
        if ($this->config()->hasAttribute('type', $configPath)) {
            $this->formatter = EaiFormatter::factory($this->config()->getAttribute('type', $configPath)
                , $this->config()->getArray($configPath)
                , $this->way);
        } else {
            $this->fault(self::getWay() . "/format : type not defined");
        }
        //Instanciation du connector
        $this->initConnector();
    }

    public function open()
    {
        $this->methodStart($this->way);

        $r = $this->formatter->connector->connect();

        $this->methodFinish($r);
        return $r;
    }

    public function close($error = false)
    {
        $this->methodStart($this->way);

        $r = $this->formatter->connector->disconnect($error);

        $this->formatter->connector->logReport();
        $this->formatter->logReport();
        if ($r) {
            $this->formatter->connector = null;
        }
        $this->methodFinish($r);
        return $r;
    }

    /**
     * @called EaiHandler.read()
     * @return array
     */
    public function fetchEaiDatas()
    {
        $this->methodStart($this->way);

        $this->eaiDatas = array();

        $fetchLine = 0;
        while ($this->formatter->fetchElements()) {

            $elements = $this->formatter->getElements();
            if (!empty($elements)) {

                foreach ($elements as $element) {
                    if (!empty($element)) {
                        $this->mapper->setEaiDataFromElement($element);

                        if ($this->mapper->getEaiData()) {
                            $this->eaiDatas[] = $this->mapper->getEaiData();
                            $fetchLine++;
                        }
                    }
                }
                //foreach

                if ($this->fetching > 0 && $fetchLine >= $this->fetching) {
                    //dump('fetchLine break', $fetchLine, $this->fetching, $this->eaiDatas);
                    break; //TODO améliorer ?
                }

            } else {
                $this->log("fetchEaiDatas: end of fetch elements (EOF)", 'debug');
            }
        }
        //while


        //dump("fetchEaiDatas:", $this->eaiDatas);
        $this->methodFinish();
        if (empty($this->eaiDatas) && !$this->formatter->getElementsFormatted() > 0) {
            $this->log("fetchEaiDatas: eaiDatas empty, but the is {$this->formatter->getElementsFormatted()} elements", 'warn');
        }
        $this->isFirst = $this->formatter->isFirst;
        $this->isLast = $this->formatter->isLast;
        return $this->eaiDatas;
    }

    /**
     *
     * @called EaiHandler.write()
     *
     * @param bool $empty_buffer: Flag indiquant que l'on est sur le dernier
     *                             appel à cette méthode
     *
     * @param bool $emptyBuffer
     */
    public function putEaiDatas($emptyBuffer = false)
    {
        $this->methodStart();
        $rawDatas = null;
        $r = false;

        if (count($this->eaiDatas) < $this->fetching and !$emptyBuffer) {
            return false;
        }
        $fetchLine = 0;

        $this->formatter->connector->isFirst = $this->formatter->isFirst = $this->isFirst;
        $this->formatter->connector->isLast = $this->formatter->isLast = $this->isLast;
        //TODO pas bon pour le last, lancé 2 fois
        //if ($this->isLast)             dump("this->isLast");

        while (list($eaiDataKey, $eaiData) = each($this->eaiDatas)) {
            if (!empty($eaiData)) {
                $elements = $this->mapper->getElementsFromEaiData($eaiData);

                if (!empty($elements)) {
                    $this->formatter->getRawFromElements($elements);
                    $rawDatas = $this->formatter->connector->getRawData();
                    $added = $this->formatter->connector->addRawDatas($rawDatas);
                    if (!$added) {
                        $this->log("putEaiDatas/getRawFromElements: generate empty rawDatas, but there is elements in source", 'warn');
                        //dump($rawDatas, $elements);
                    }
                } else {
                    $this->log("putEaiDatas/getElementsFromEaiData: generate empty elements , but there is eaiData in source", 'warn');
                    //dump($elements, $eaiData);
                }
            } else {
                $this->log("putEaiDatas: having an empty eaiData source row", 'warn');
                //dump($eaiData);
            }
            unset($this->eaiDatas[$eaiDataKey]);

            $fetchLine++;
            if ($this->fetching > 0 and $fetchLine >= $this->fetching and !$emptyBuffer) {
                break;
            }

        }
        //while

        //TODO: Pas terrible .. utilisé uniquement pour le XML
        //génère une incohérence sur rawDatasWrited <=>rawDatasFetched
        if ($emptyBuffer) {
            $rawDatas = $this->formatter->getRawFromElements(false, $emptyBuffer);

            $this->formatter->connector->addRawDatas($rawDatas);

        }

        $r = $this->formatter->connector->writeRawDatas($this->mode);

        if (!$r) {
            //TODO:stockage de la donnée en erreur ...
            // utilisation de l'objet EaiDatas
            $this->log('writeRawDatas() return false. EOF ?', 'debug');
        }


        $this->methodFinish();

        return $r;
    }

    public function setEaiDatas($eaiDatas)
    {
        $this->eaiDatas = array_merge($this->eaiDatas, $eaiDatas); ///?????????????
    }


}

//class
?>