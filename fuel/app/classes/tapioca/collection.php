<?php

namespace Tapioca;

use FuelException;
use Config;

class TapiocaCollectionException extends FuelException {}

class Collection
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  object  Active group
	 */
	protected static $group = null;

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
	 * @var  array 	Cache Summary where clause
	 */
	protected static $summary_where = array();

	/**
	 * @var  array  Events list for callbacks
	 */
	protected $callback = array();

	/**
	 * @var  array  List of fields who need to be cast, get from config
	 */
	protected static $castable = array();

	/**
	 * @var  array  path and value of fields to cast
	 */
	protected $castablePath = array();

	/**
	 * @var  array  path and label of summary fields
	 */
	protected $summaryPath = array();

	protected $summaryEdit = 0;

	/**
	 * @var  array  path and label of fieds who need validation
	 */
	protected $rulesPath = array();

	/**
	 * Loads in the Collection object
	 *
	 * @param   object  Group instace
	 * @param   MongoId|string  Collection id or Name Column value
	 * @return  void
	 * @throws  TapiocaCollectionException
	 */
	public function __construct(\Auth\Group $group, $id = null, $check_exists = false)
	{
		// load and set config
		static::$group         = $group;
		static::$collection    = strtolower(Config::get('tapioca.collections.collections'));		
		static::$db            = \Mongo_Db::instance();

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
				'app_id' => static::$group->get('slug')
			), 1);

			// if there was a result 
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
								'app_id'    => static::$group->get('slug'),
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


				if(isset($summary[0]['callback']))
				{
					$this->callback = $summary[0]['callback'];
				}

				$this->set_summary_where();

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
	 * Format "where" query for the current collection.
	 *
	 * @return  void
	 */
	private function set_summary_where()
	{
		static::$summary_where = array( 'app_id'    => static::$group->get('slug'),
										'namespace' => $this->namespace,
										'type'      => 'summary');		
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
	 * Gets the summaries of all collections,
	 * only admins can see non published (status 100) Collection
	 *
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function all($status = 100, \Auth\User $user)
	{
		//query database for collections's summaries
		$ret = static::$db
				->select(array(), array(
					'revisions'
				))
				->get_where(static::$collection, array(
					'app_id' => static::$group->get('slug'),
					'type'   => 'summary',
					'status' => array('$gte' => (int) $status)
				));

		if($ret)
		{
			// Is User is an admin
			$user_id  = $user->get('id');
			$editable = (static::$group->is_admin($user_id));

			foreach($ret as &$result)
			{
				$result['editable'] = $editable;
				unset($result['_id']);
			}
		}

		return $ret;
	}

	/**
	 * Gets the current collections definition
	 *
	 * @params  int Revision number 
	 * @return  array
	 * @throws  TapiocaException
	 */

	public function get(int $revision = null, \Auth\User $user)
	{
		$data       = $this->data($revision);

		// Format return
		$ret            = array_merge($this->summary, $data);
		$ret['created'] = (int) $ret['created']->sec;

		unset($ret['type']);

		// Is User is an admin
		$user_id = $user->get('id');
		$ret['editable'] = (static::$group->is_admin($user_id));

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
			// revisons array is zero based index
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

	/**
	 * check for required fields
	 *
	 * @param   array  Fields to update
	 * @param   string list to check
	 * @return  bool
	 * @throws  TapiocaException
	 */
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
			'app_id' => static::$group->get('slug'),
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

		$this->set_summary_where();

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

		if(isset($fields['status']))
		{
			$fields['status'] = (int) $fields['status'];
		}

		$update =  static::$db
						->where(static::$summary_where)
						->update(static::$collection, $fields);

		if($update)
		{
			$this->summary = array_merge($this->summary, $fields);

			return true;
		}

		return false;
	}

	/**
	 * Add a new structure revision to the current collection
	 *
	 * @param   array  Fields 
	 * @param   object User object instance 
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function update_data(array $fields, \Auth\User $user)
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		// check for required fields
		$check_list = Config::get('tapioca.validation.collection.data');
		
		self::validation($fields, $check_list);

		static::$castable = Config::get('tapioca.cast');

		$this->summaryEdit = $fields['summaryEdit'];

		$this->parse($fields['structure']);

		$arrData    = Config::get('tapioca.collection.dispatch.data');

		$revision = (count($this->data) + 1);

		$data = array(
			'app_id'      => static::$group->get('slug'),
			'type'        => 'data',
			'namespace'   => $this->namespace,
			'revision'    => $revision,
			'summary'     => ($this->summaryEdit) ? $fields['summary'] : $this->summaryPath,
			'summaryEdit' => $this->summaryEdit,
			'cast'        => $this->castablePath,
			'rules'       => $this->rulesPath,
			'structure'   => $fields['structure'],
			'callback'    => $fields['callback']
		);

		$revision = array(
			'revison' => $revision,
			'date'    => new \MongoDate(),
			'user'    => $user->get('id'),
			'status'  => (int) 100 
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
								->where(static::$summary_where)
								->update(static::$collection, array(
									'revisions' => $this->summary['revisions'],
									'summary'   => $data['summary']
								));

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
	 * Increment/decrement total documents in Collection
	 *
	 * @param   int   Increment|Decrement
	 * @return  void
	 */
	public function inc_document($direction = 1)
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		$ret = static::$db->command(
					array('findandmodify' => static::$collection,
						  'query' => static::$summary_where,
						  'update' => array('$inc' => array('documents' => (int) $direction)),
						  'new' => TRUE
					)
				);

		if($ret['ok'] == 1)
		{
			$this->summary['documents'] = $ret['value']['documents'];
		}
	}

	/**
	 * Reset total documents in Collection
	 *
	 * @return  void
	 */
	public function reset_document()
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}
		
		$update = static::$db
					->where(static::$summary_where)
					->update(static::$collection, array('$set' => array('documents' => (int) 0)), array(), true);

		if($update)
		{
			$this->summary['documents'] = (int) 0;
		}
	}

	/**
	 * Delete the current collection
	 *
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function delete()
	{
		if(is_null($this->summary))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		return static::$db
					->where(array(
							'namespace' => $this->namespace,
							'app_id'    => static::$group->get('slug')
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
												'app_id'    => static::$group->get('slug'),
												'namespace' => $namespace,
												'type'      => 'summary'
											), 1);
		if (count($result) == 1)
		{
			return $result[0];
		}

		return false;
	}

	/**
	 * Collection fields who need a post traitment
	 *
	 * @param   object 
	 * @param   string 	use for recustion
	 * @return  bool
	 */
	private function parse(&$schema, $path = '/')
	{
		foreach($schema as &$item)
		{
			if($item['type'] == 'object' || $item['type'] == 'array')
			{
				$tmp_path = $path.$item['id'].'/';
				$this->parse($item['node'], $tmp_path);
			}
			else
			{
				$tmp_path = $path.$item['id'];

				// cast fields
				if(in_array($item['type'], static::$castable))
				{
					$obj = new \stdClass;
					$obj->path = $tmp_path;
					$obj->type = $item['type'];

					$this->castablePath[] = $obj;

					if($item['type'] == 'number' && !isset( $item['rules']))
					{
						$item['rules'] = array('numeric');
					}

					if(!in_array('numeric', $item['rules']))
					{
						$item['rules'][] = 'numeric';
					}
				}

				// summary
				if(!$this->summaryEdit) // if we don't set summary mannualy
				{
					if(isset($item['summary']) && $item['summary'])
					{
						$itemPath = static::setItemPath($path, $item['id']);

						$obj = new \stdClass;
						$obj->path  = $itemPath;
						$obj->label = $item['label'];

						$this->summaryPath[] = $obj;

						if(!isset( $item['rules']))
						{
							$item['rules'] = array('required');
						}

						if(!in_array('required', $item['rules']))
						{
							$item['rules'][] = 'required';
						}	
					}
				}

				// rules
				if(isset($item['rules']) && count($item['rules']) > 0)
				{
					$obj = new \stdClass;
					$obj->path  = $tmp_path;
					$obj->rules = $item['rules'];

					$this->rulesPath[] = $obj;
				}
			}
		}
	}

	/**
	 * Format field path
	 *
	 * @param   string  xpath
	 * @param   string 	ite ID
	 * @return  string
	 */
	private static function setItemPath($path, $id)
	{
		$itemPath = $path.$id;
		$itemPath = str_replace('/', '.', $itemPath);
		$itemPath = substr($itemPath, 1); // remove root

		return $itemPath;
	}
}