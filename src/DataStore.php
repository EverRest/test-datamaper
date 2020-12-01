<?php

//A super-simple replacement class for a real database, just so we have a place for storing results.
/**
 * Class DataStore
 */
class DataStore
{
    /**
     * @var null
     */
    protected $_storePath = null;
    /**
     * @var array|mixed|null
     */
    protected $_dataStore = array();

    /**
     * DataStore constructor.
     * @param $storePath
     * @throws Exception
     */
    public function __construct($storePath)
    {
        $this->_storePath = $storePath;
        if (!file_exists($storePath)) {
            if (!touch($storePath)) {
                throw new Exception("Could not create data store file $storePath. Details:" . getLastError());
            }
            if (!chmod($storePath, 0777)) {
                throw new Exception("Could not set read/write on data store file $storePath. " .
                    "Details:" . getLastError());
            }
        }
        if (!is_readable($storePath) || !is_writable($storePath)) {
            throw new Exception("Data store file $storePath must be readable/writable. Details:" . getlastError());
        }
        $rawData = file_get_contents($storePath);

        if ($rawData === false) {
            throw new Exception("Read of data store file $storePath failed.  Details:" . getLastError());
        }
        if (strlen($rawData > 0)) {
            $this->_dataStore = unserialize($rawData);
        } else {
            $this->_dataStore = null;
        }
    }

    //update the store with information
    /**
     * @param $item
     * @param $primary
     * @param $data
     */
    public function set($item, $primary, $data)
    {
        $foundItem = null;
        $this->_dataStore[$item][$primary] = $data;
    }

    //get information
    /**
     * @param $item
     * @param $primary
     * @return mixed|null
     */
    public function get($item, $primary)
    {
        if (isset($this->_dataStore[$item][$primary])) {
            return $this->_dataStore[$item][$primary];
        } else {
            return null;
        }
    }

    //delete an item.
    /**
     * @param $item
     * @param $primary
     */
    public function delete($item, $primary)
    {
        if (isset($this->_dataStore[$item][$primary])) {
            unset($this->_dataStore[$item][$primary]);
        }
    }

    //save everything
    /**
     * @throws Exception
     */
    public function save()
    {
        $result = file_put_contents($this->_storePath, serialize($this->_dataStore));
        if ($result === null) {
            throw new Exception("Write of data store file $this->_storePath failed.  Details:" . getLastError());
        }
    }

    //Which types of items do we have stored
    /**
     * @return array
     */
    public function getItemTypes()
    {
        if (is_null($this->_dataStore)) {
            return array();
        }
        return array_keys($this->_dataStore);
    }

    //get keys for an item-type, so we can loop over.
    /**
     * @param $itemType
     * @return array
     */
    public function getItemKeys($itemType)
    {
        return array_keys($this->_dataStore[$itemType]);
    }
}