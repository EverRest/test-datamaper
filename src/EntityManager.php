<?php

//This class managed in-memory entities and commmunicates with the storage class (DataStore in our case).
class EntityManager
{
    /**
     * @var array
     */
    protected $_entities = array();
    /**
     * @var array
     */
    protected $_entityIdToPrimary = array();
    /**
     * @var array
     */
    protected $_entityPrimaryToId = array();
    /**
     * @var array
     */
    protected $_entitySaveList = array();
    /**
     * @var int|null
     */
    protected $_nextId = null;
    /**
     * @var DataStore|null
     */
    protected $_dataStore = null;

    /**
     * EntityManager constructor.
     * @param $storePath
     * @throws Exception
     */
    public function __construct($storePath)
    {
        $this->_dataStore = new DataStore($storePath);

        $this->_nextId = 1;

        $itemTypes = $this->_dataStore->getItemTypes();
        foreach ($itemTypes as $itemType)
        {
            $itemKeys = $this->_dataStore->getItemKeys();
            foreach ($itemKeys as $itemKey) {
                $entity = $this->create($itemType, $this->_dataStore->get($itemType, $itemKey), true);
            }
        }
    }

    //create an entity

    /**
     * @param $entityName
     * @param $data
     * @param false $fromStore
     * @return mixed
     */
    public function create($entityName, $data, $fromStore = false)
    {
        $entity = new $entityName;
        $entity->_entityName = $entityName;
        $entity->_data = $data;
        $entity->_em = Entity::getDefaultEntityManager();
        $id = $entity->_id = $this->_nextId++;
        $this->_entities[$id] = $entity;
        $primary = $data[$entity->getPrimary()];
        $this->_entityIdToPrimary[$id] = $primary;
        $this->_entityPrimaryToId[$primary] = $id;
        if ($fromStore !== true) {
            $this->_entitySaveList[] = $id;
        }

        return $entity;
    }

    //update
    /**
     * @param $entity
     * @param $newData
     * @return mixed
     */
    public function update($entity, $newData)
    {
        if ($newData === $entity->_data) {
            //Nothing to do
            return $entity;
        }

        $this->_entitySaveList[] = $entity->_id;
        $oldPrimary = $entity->{$entity->getPrimary()};
        $newPrimary = $newData[$entity->getPrimary()];
        if ($oldPrimary != $newPrimary)
        {
            $this->_dataStore->delete(get_class($entity),$oldPrimary);
            unset($this->_entityPrimaryToId[$oldPrimary]);
            $this->_entityIdToPrimary[$entity->$id] = $newPrimary;
            $this->_entityPrimaryToId[$newPrimary] = $entity->$id;
        }
        $entity->_data = $newData;

        return $entity;
    }

    //Delete
    /**
     * @param $entity
     * @return null
     */
    public function delete($entity)
    {
        $id = $entity->_id;
        $entity->_id = null;
        $entity->_data = null;
        $entity->_em = null;
        $this->_entities[$id] = null;
        $primary = $entity->{$entity->getPrimary()};
        $this->_dataStore->delete(get_class($entity),$primary);
        unset($this->_entityIdToPrimary[$id]);
        unset($this->_entityPrimaryToId[$primary]);
        return null;
    }

    /**
     * @param $entity
     * @param $primary
     * @return mixed|null
     */
    public function findByPrimary($entity, $primary)
    {
        if (isset($this->_entityPrimaryToId[$primary])) {
            $id = $this->_entityPrimaryToId[$primary];
            return $this->_entities[$id];
        } else {
            return null;
        }
    }

    //Update the datastore to update itself and save.
    /**
     * @throws Exception
     */
    public function updateStore() {
        foreach($this->_entitySaveList as $id) {
            $entity = $this->_entities[$id];
            $this->_dataStore->set(get_class($entity),$entity->{$entity->getPrimary()},$entity->_data);
        }
        $this->_dataStore->save();
    }
}