<?php
/**
 *
 * @todo all
 * @author
 *
 */
class EaiConnectorRest extends EaiConnector
{
    /**
     * Ouverture du connector
     *
     * @return bool
     */
    public function _eaiConnect()
    {
        return true;
    }

    /**
     * Fermeture du connector
     *
     * @return bool
     */
    public function _eaiDisconnect($error= false)
    {
        return true;
    }

    /**
     * Lecture et retourne de la donnée brute
     *
     * @return $rawData
     */
    public function _eaiFetchRawData()
    {
        return true;
    }

    /**
     * Ecriture de la donnée brute $this->rawData
     *
     * @param mixed $rawData
     */
    public function _eaiWriteRawData()
    {
        return true;
    }
	public function _eaiWriteRawDatas()
	{
		$this->methodStart();
		foreach ($this->rawDatas as $rawData) {
			$this->setRawData($rawData);
			$this->_eaiWriteRawData();
		}
		$this->methodFinish();

		return true;
	}

}//class