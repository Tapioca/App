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
	protected $app_id = null;

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
	public function __construct($app_id, $id = null, $check_exists = false)
	{
		// load and set config
		static::$collection = strtolower(Config::get('tapioca.tables.collections'));
		
		$this->app_id = $app_id;

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
				'app_id' => $this->app_id
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
								'app_id'    => $this->app_id,
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
			'app_id' => $this->app_id,
			'type'  => 'summary'
		));
	}

	/**
	 * Gets the current collections definition
	 *
	 * @params  int Revision number 
	 * @return  array
	 * @throws  TapiocaException
	 */

	public function get($revision = null)
	{
		$data       = $this->data($revision);

		// Format return
		$ret            = array_merge($this->summary, $data);
		$ret['created'] = (int) $ret['created']->sec;

		unset($ret['type']);

		return $ret;
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
	 * if no revison ID set, return last revision
	 *
	 * @param   int Revision number
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function data($revision = null)
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

		return end($this->data);
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

		$namespace = (!is_null($fields['namespace'])) ? $fields['namespace'] : $fields['name'];

		$fields['namespace'] = \Inflector::friendly_title($namespace, '-', true);

		if($this->namespance_exists($fields['namespace']))
		{
			throw new \TapiocaException(
				__('tapioca.collection_already_exists', array('name' => $fields['name']))
			);
		}

		// check for required fields
		$check_list = Config::get('tapioca.validation.collection.summary');
		
		self::validation($fields, $check_list);

		$status = (int) 1;

		if(isset($fields['status']))
		{
			$status = (int) $fields['status'];
			unset($fields['status']);
		}

		$new_summary = array(
			'app_id' => $this->app_id,
			'type' => 'summary',
			'documents' => (int) 0,
			'status' => $status,
			'created' => new \MongoDate(),
			'revisions' => array()
		) + $fields;

		$this->summary   = $new_summary;
		$this->namespace = $new_summary['namespace'];
		$this->name      = $new_summary['name'];
		$this->data      = array();

		return static::$db->insert(static::$collection, $new_summary);
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
							'app_id'    => $this->app_id,
							'namespace' => $this->namespace,
							'type'      => 'summary'
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

		$arrData    = Config::get('tapioca.collection.dispatch.data');

		$revision = (count($this->data) + 1);

		$data = array(
			'app_id' => $this->app_id,
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
			$this->data[] = $data;

			$update_summary = static::$db
								->where(array(
									'app_id' => $this->app_id,
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

	public function delete()
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		return static::$db
					->where(array(
							'namespace' => $this->namespace,
							'app_id'    => $this->app_id
					))
					->delete_all(static::$collection);
	}

	/**
	 * Check if namespace exists already
	 *
	 * @param   string  The namespace value
	 * @return  bool
	 */
	private function namespance_exists($namespace)
	{
		// query db to check for login_column
		$result = static::$db->get_where(static::$collection, array(
			'namespace' => $namespace,
			'app_id' => $this->app_id
		), 1);

		if (count($result) == 1)
		{
			return $result[0];
		}

		return false;
	}
}