<?php

namespace Tapioca;

use FuelException;
use Config;

class TapiocaCollectionException extends \FuelException {}

class Tapioca_Collection
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

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
	public function __construct($id = null, $check_exists = false)
	{
		// load and set config

		static::$collection = strtolower(Config::get('tapioca.tables.collections'));

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
				$field = '_namespace';
			}

			//query database for collection's summary
			$summary = static::$db->get_where(static::$collection, array(
				$field => $id,
				'_type' => 'summary'
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
									'_namespace' => $summary[0]['_namespace'],
									'_type' => 'data'
							))
							->order_by(array(
								'_revision' => 'asc'
							))
							->get(static::$collection);


				$this->summary = $summary[0];
				$this->data = $data;
				$this->namespace = $summary[0]['_namespace'];
				$this->name = $summary[0]['_about']['name'];
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
	 * Gets the summaries of all collections
	 *
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function all()
	{
		//query database for collections's summaries
		return static::$db->get_where(static::$collection, array(
			'_type' => 'summary'
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
}