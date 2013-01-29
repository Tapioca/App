<?php
/**
 * Tapioca: Schema Driven Data Engine 
 * Flexible CMS build on top of MongoDB, FuelPHP and Backbone.js
 *
 * @package   Tapioca
 * @version   v0.8
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/App
 */

class Mongo_Db extends Fuel\Core\Mongo_Db
{
    /**
    *   --------------------------------------------------------------------------------
    *   // Hash
    *   --------------------------------------------------------------------------------
    *
    *   Get the documents based upon the passed parameters.
    *   Return hash
    *
    */
    
     public function hash($collection = "", $unsetId = false)
     {
        if(empty($collection))
        {
            throw new \Mongo_DbException('In order to retrieve documents from MongoDB, a collection name must be passed');
        }

        $cursor     = $this->db->{$collection}->find($this->wheres, $this->selects);

        $cursor->immortal(true);
        
        $total      = $cursor->count();
        $documents  = $cursor->limit((int) $this->limit)->skip((int) $this->offset)->sort($this->sorts);
        
        $returns = array();

        if ($documents and ! empty($documents))
        {
            foreach ($documents as $doc)
            {
                if($unsetId)
                {
                    unset($doc['_id']);
                }

                $returns[] = $doc;
            }
        }

        $this->_clear();

        $obj            = new stdClass;
        $obj->total     = $total;
        $obj->skip      = $this->offset;
        $obj->limit     = $this->limit;
        $obj->results   = $returns;
        
        return $obj;
    }
}