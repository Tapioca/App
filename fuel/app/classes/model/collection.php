<?php

namespace Model;

use Config;
use Mongo_Db;
use FuelException;

class CollectionException extends \FuelException {}
class CollectionNotFoundException extends \Model\CollectionException {}

class Collection extends \Model
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  array  User
	 */
	protected $collection = array();

	/**
	 * @var  string  Table name
	 */
	protected static $table = null;

	/**
	 * Loads in the Collection object
	 *
	 * @param   MongoId|string  Collection id or Name Column value
	 * @return  void
	 * @throws  CollectionNotFoundException
	 */
	public function __construct($id = null, $check_exists = false)
	{
		// load and set config

		static::$table = strtolower(Config::get('tapioca.tables.collections'));

		static::$db = \Mongo_Db::instance();

		// if an ID was passed
		if ($id)
		{
			// make sure ID is a MongoID
			if(! ($id instanceof \MongoId))
			{
				// set field to login_column
				$field = '_id';
			}
			// if ID is not an MongoID
			else
			{
				// set field to namespace for query
				$field = 'namespace';
			}

			//query database for collection
			$collection = static::$db->get_where(static::$table, array(
				$field => $id
			), 1);

			// if there was a result - update user
			if (count($collection) == 1)
			{
				// if just a collection exists check - return true, no need for additional queries
				if ($check_exists)
				{
					return true;
				}

				$this->collection = $collection[0];
			}
			// collection doesn't exist
			else
			{
				throw new \Model\CollectionNotFoundException('collection_not_found');
			}
		}
	}
}
