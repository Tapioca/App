<?php

namespace Tapioca;

use FuelException;
use Config;

class TapiocaCollectionException extends \FuelException {}

class Collection
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  array  Group's id
	 */
	protected $appid = null;

	/**
	 * @var  array  Collection's name for exception message
	 */
	protected $name = null;

	/**
	 * @var  array  Collection's namespace for exception message
	 */
	protected $namespace = null;

	/**
	 * @var  array  Collection's summary
	 */
	protected $summary = null;

	/**
	 * @var  array  last collection's revision + summary
	 */
	protected $combine = null;

	/**
	 * @var  array  Collection's data
	 */
	protected $data = array();

	/**
	 * @var  string  MongoDb collection's name
	 */
	protected static $collection = null;

	/**
	 * Loads in the Collection object
	 *
	 * @param   MongoId|string  Collection id or Name Column value
	 * @return  void
	 * @throws  TapiocaCollectionException
	 */
	public function __construct($appid, $id = null, $check_exists = false)
	{
		// load and set config
		static::$collection = strtolower(Config::get('tapioca.tables.collections'));
		
		$this->appid = $appid;

		static::$db = \Mongo_Db::instance();

		// if an ID was passed
		if ($id)
		{
			// make sure ID is a MongoID
			if($id instanceof \MongoId)
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

			//query database for collection's summary
			$summary = static::$db->get_where(static::$collection, array(
				$field  => $id,
				'type'  => 'summary',
				'appid' => $this->appid
			), 1);

			// if there was a result - update user
			if (count($summary) == 1)
			{
				// if just a collection exists check - return true, no need for additional queries
				if ($check_exists)
				{
					return true;
				}

				//query database for collection's summary
				$data = static::$db
							->where(array(
								'appid'     => $this->appid,
								'namespace' => $summary[0]['namespace'],
								'type'      => 'data'
							))
							->order_by(array(
								'revision'  => 'asc'
							))
							->get(static::$collection);

				$this->summary   = $summary[0];
				$this->data      = $data;
				$this->namespace = $summary[0]['namespace'];
				$this->name      = $summary[0]['name'];


				//$this->combine   
			}
			// collection doesn't exist
			else
			{
				throw new \TapiocaCollectionException(
					__('tapioca.collection_not_found', array('collection' => $id))
				);
			}
		}
	}

	/**
	 * Magic get method to allow getting class properties but still having them protected
	 * to disallow writing.
	 *
	 * @return  mixed
	 */
	public function __get($property)
	{
		return $this->$property;
	}

	/**
	 * Gets the summaries of all collections
	 *
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function all()
	{
		//query database for collections's summaries
		return static::$db->get_where(static::$collection, array(
			'appid' => $this->appid,
			'type'  => 'summary'
		));
	}

	/**
	 * Gets the summary of the current collection
	 *
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function summary()
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}
		return $this->summary;
	}

	/**
	 * Gets the data of the current collection
	 *
	 * @param   int Revision number
	 * @param   int Revision status
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function data($revision = null, $status = 100)
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		// get a specific revison
		if(!is_null($revision))
		{
			// revisons is a zero based index
			--$revision;

			// revision exists
			if(isset($this->data[$revision]))
			{
				return $this->data[$revision];
			}

			throw new \TapiocaException(
				__('tapioca.collection_revision_not_found', array('collection' => $this->name, 'revision' => $revision))
			);
		}

		return $this->data;
	}

	private static function validation(array $fields, $check_list)
	{
		foreach($check_list as $item)
		{
			if(!isset($fields[$item]) || empty($fields[$item]))
			{
				throw new \TapiocaException(
					__('tapioca.collection_column_is_empty', array('column' => $item))
				);
			}
		}		
	}

	/**
	 * Create collection's summary
	 *
	 * @param   array  Fields
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function create_summary(array $fields)
	{
		// check for required fields
		$check_list = Config::get('tapioca.validation.collection.summary');
		
		self::validation($fields, $check_list);

		$namespace = \Inflector::friendly_title($fields['namespace'], '-', true);

		if(!self::namespance_exists($namespace))
		{
			throw new \TapiocaException(
				__('tapioca.collection_already_exists', array('name' => $fields['name']))
			);
		}

		return static::$db->insert(static::$collection, $fields);
	}

	/**
	 * Update the current collection's summary
	 *
	 * @param   array  Fields to update
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function update_summary(array $fields)
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		// check for required fields
		$check_list = Config::get('tapioca.validation.collection.summary');
		
		self::validation($fields, $check_list);

		return static::$db
					->where(array(
							'namespace' => $this->namespace,
							'type' => 'summary'
					))
					->update(static::$collection, $fields);
	}

	public function update_data(array $fields, $user)
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		// check for required fields
		$check_list = Config::get('tapioca.validation.collection.data');
		
		self::validation($fields, $check_list);

		$revision = (count($this->data) + 1);

		$data = array(
			'appid' => $this->appid,
			'type' => 'data',
			'namespace' => $this->namespace,
			'revision' => $revision,
		) + $fields;

		$revision = array(
			'revison' => $revision,
			'date' => new \MongoDate(),
			'user' => $user,
			'status' => (int) 100 
		);

		$insert_data = static::$db->insert(static::$collection, $data);

		if($insert_data)
		{
			//update previous revisions status
			foreach($this->summary['revisions'] as &$r)
			{
				$r['status'] = -1;
			}
			
			$this->summary['revisions'][] = $revision;

			$update_summary = static::$db
								->where(array(
									'appid' => $this->appid,
									'namespace' => $this->namespace,
									'type' => 'summary'
								))
								->update(static::$collection, array('revisions' => $this->summary['revisions']));

			if(!$update_summary)
			{
				throw new \TapiocaException(
					__('tapioca.can_not_update_collection_revision', array('name' => $this->name))
				);				
			}

			return true;
		}

		throw new \TapiocaException(
			__('tapioca.can_not_insert_collection_data', array('name' => $this->name))
		);
	}

	/**
	 * Check if namespace exists already
	 *
	 * @param   string  The namespace value
	 * @return  bool
	 */
	protected static function namespance_exists($namespace)
	{
		// query db to check for login_column
		$result = static::$db->get_where(static::$collection, array(
			'namespace' => $namespace,
			'appid' => $this->$appid
		), 1);

		if (count($result) == 1)
		{
			return $result[0];
		}

		return false;
	}
}