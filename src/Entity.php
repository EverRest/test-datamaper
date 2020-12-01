<?php

//abstract base class for in-memory representation of various business entities.  The only item
//we have implemented at this point is InventoryItem (see below).

/**
 * Class Entity
 */
abstract class Entity
{
    /**
     * @var null
     */
    static protected $_defaultEntityManager = null;
    /**
     * @var null
     */
    protected $_data = null;
    /**
     * @var null
     */
    protected $_em = null;
    /**
     * @var null
     */
    protected $_entityName = null;
    /**
     * @var null
     */
    protected $_id = null;

    /**
     *
     */
    public function init() {}

    /**
     * @return mixed
     */
    abstract public function getMembers();

    /**
     * @return mixed
     */
    abstract public function getPrimary();

    //setter for properies and items in the underlying data array
    /**
     * @param $variableName
     * @param $value
     * @throws Exception
     */
    public function __set($variableName, $value)
    {
        if (array_key_exists($variableName, array_change_key_case($this->getMembers()))) {
            $newData = $this->_data;
            $newData[$variableName] = $value;
            $this->_update($newData);
            $this->_data = $newData;
        } else {
            if (property_exists($this, $variableName)) {
                $this->$variableName = $value;
            } else {
                throw new Exception("Set failed. Class " . get_class($this) .
                " does not have a member named " . $variableName . ".");
            }
        }
    }

    //getter for properies and items in the underlying data array
    /**
     * @param $variableName
     * @return mixed
     * @throws Exception
     */
    public function __get($variableName)
    {
        if (array_key_exists($variableName, array_change_key_case($this->getMembers()))) {
            $data = $this->read();
            return $data[$variableName];
        } else {
            if (property_exists($this, $variableName)) {
                return $this->$variableName;
            } else {
                throw new Exception("Get failed. Class " . get_class($this) .
                " does not have a member named " . $variableName . ".");
            }
        }
    }

    /**
     * @param $em
     */
    static public function setDefaultEntityManager($em)
    {
        self::$_defaultEntityManager = $em;
    }

    //Factory function for making entities.
    /**
     * @param $entityName
     * @param $data
     * @param null $entityManager
     * @return mixed
     */
    static public function getEntity($entityName, $data, $entityManager = null)
    {
        $em = $entityManager === null ? self::$_defaultEntityManager : $entityManager;
        $entity = $em->create($entityName, $data);
        $entity->init();

        return $entity;
    }

    /**
     * @return null
     */
    static public function getDefaultEntityManager()
    {
        return self::$_defaultEntityManager;
    }

    /**
     * @param $entityName
     * @param $data
     * @return mixed
     */
    public function create($entityName, $data)
    {
        $entity = self::getEntity($entityName, $data);
        return $entity;
    }

    /**
     * @return null
     */
    public function read()
    {
        return $this->_data;
    }

    /**
     * @param $newData
     */
    public function update($newData)
    {
        $this->_em->update($this, $newData);
        $this->_data = $newData;
    }

    /**
     *
     */
    public function delete()
    {
        $this->_em->delete($this);
    }
}