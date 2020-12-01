<?php

//An example entity, which some business logic.  we can tell inventory items that they have shipped or been received
//in
class InventoryItem extends Entity
{
    //Update the number of items, because we have shipped some.
    /**
     * @param $numberShipped
     */
    public function itemsHaveShipped($numberShipped)
    {
        $current = $this->qoh;
        $current -= $numberShipped;
        $newData = $this->_data;
        $newData['qoh'] = $current;
        $this->update($newData);
    }

    //We received new items, update the count.
    /**
     * @param $numberReceived
     */
    public function itemsReceived($numberReceived)
    {
        $newData = $this->_data;
        $current = $this->qoh;

        for($i = 1; $i <= $numberReceived; $i++) {
            //notifyWareHouse();  //Not implemented yet.
            $newData['qoh'] = $current++;
        }
        $this->update($newData);
    }

    /**
     * @param $salePrice
     */
    public function changeSalePrice($salePrice)
    {
        $newData = $this->_data;
        $newData['salePrice'] = $this->update($newData);
    }

    /**
     * @return int[]
     */
    public function getMembers()
    {
        //These are the field in the underlying data array
        return array("sku" => 1, "qoh" => 1, "cost" => 1, "salePrice" => 1)    ;
    }

    /**
     * @return string
     */
    public function getPrimary()
    {
        //Which field constitutes the primary key in the storage class?
        return "sku";
    }
}