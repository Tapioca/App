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
     * Magic get method to allow getting class properties but still having them protected
     * to disallow writing.
     *
     * @return  mixed
     */
    public function __get( $name )
    {
        return $this->{ $name };
    }

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

        $obj            = new stdClass;
        $obj->total     = $total;
        $obj->skip      = $this->offset;
        $obj->limit     = $this->limit;
        $obj->results   = $returns;

        $this->_clear();
        
        return $obj;
    }

    public function listCollections()
    {
        return $this->db->listCollections();
    }

    public function selectCollection( $collection )
    {
        return $this->db->selectCollection( $collection );
    }
}