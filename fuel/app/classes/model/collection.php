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


	/**
	 * Checks if the Field is set or not.
	 *
	 * @param   string  Field name
	 * @return  bool
	 */
	public function __isset($field)
	{
		return array_key_exists($field, $this->user);
	}

	/**
	 * Gets a field value of the user
	 *
	 * @param   string  Field name
	 * @return  mixed
	 * @throws  CollectionException
	 */
	public function __get($field)
	{
		return $this->get($field);
	}

	/**
	 * Create's a new collection.  Returns user '_id'.
	 *
	 * @param   array  Collection array for creation
	 * @return  int
	 * @throws  CollectionException
	 */
	public function create(array $collection)
	{
		// check for required fields

		$check_list = array('namespace', 'name', 'desc', 'structure', 'dependencies', 'summary', 'indexes', 'callbacks', 'appid');
		
		foreach($check_list as $field)
		{
			if(!isset($collection[$field]) || empty($collection[$field]))
			{
				throw new \Model\CollectionException('required_field_empty: '.$field);
			}
		}

		// check to see if namespace is already taken
		$namespace_exists = $this->namespace_exists($collection['namespace']);

		if (!$namespace_exists)
		{
			throw new \Model\CollectionException('namespace_exists');
		}

		$decode_list = array('structure', 'dependencies', 'summary', 'indexes', 'callbacks');
		
		foreach($decode_list as $field)
		{
			$value  = json_decode($collection[$field]);
			$$field = (is_null($value)) ? $collection[$field] : $value;
		}

		$about['created']	= new \MongoDate();
		$about['documents']	= (int) 0;
		$about['name']		= $collection['name'];
		$about['desc']		= $collection['desc'];
		$about['status']	= $collection['status'];
		$about['preview']	= $collection['preview'];

		// set new collection values
		$new_collection = array(
			'namespace'		=> $collection['namespace'], 
			'appid'			=> $collection['appid'],
			'about'			=> $about,
			'structure'		=> $structure, 
			'dependencies'	=> $dependencies,
			'summary'		=> $summary,
			'indexes'		=> $indexes,
			'callbacks'		=> $callbacks,
		);



		$new_collection = array(
			'email' => $user['email'],
			'password' => $this->generate_password($user['password']),
			'created_at' => new \MongoDate(),
			'activated' => ($activation) ? 0 : 1,
			'status' => 1,
			'remember_me' => null,
			'password_reset_hash' => null,
			'is_admin' => 0,
			'level' => 0,
		) + $user;

		// insert new collection and return _id
		return static::$db->insert(static::$table, $new_collection);
	}

	/**
	 * Check if namespace exists already
	 *
	 * @param   string  The namespace value
	 * @return  bool
	 */
	protected function namespance_exists($namespace)
	{
		// query db to check for login_column
		$result = static::$db->get_where(static::$table, array(
			'namespace' => $namespace
		), 1);

		if (count($result) == 1)
		{
			return $result[0];
		}

		return false;
	}
}
