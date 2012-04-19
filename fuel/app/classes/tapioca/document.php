<?php

namespace Tapioca;

use FuelException;
use Config;

class TapiocaDocumentException extends \FuelException {}

class Document
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  string  MongoDb collection's name
	 */
	protected static $collection = null;

	/**
	 * @var  string  Tapioca collection's namespace
	 */
	protected static $namespace = null;

	/**
	 * @var  string  Document ref
	 */
	protected static $ref = null;

	/**
	 * @var  int  active Document version
	 */
	protected static $active = null;

	/**
	 * @var  array  selected Document
	 */
	protected $document = null;

	/**
	 * @var  array  selected Document's summary
	 */
	protected $summary = null;

	/**
	 * @var  string  Query arguments
	 */
	protected static $operators = array('select', 'where', 'sort', 'limit', 'skip');
	protected $select = array();
	protected $where  = array('_about.status' => array('$ne' => -1));
	protected $sort   = array('_about.revision' => 'desc');	
	protected $limit  = 99999;
	protected $skip   = 0;

	/**
	 * Loads in the Document object
	 *
	 * @param   string  Group id
	 * @param   string  Collection namespace
	 * @param   string  Document ref
	 * @return  void
	 */
	public function __construct($app_slug, $namespace, $ref = null, $check_exists = false)
	{
		// load and set config
		static::$collection = $app_slug.'-'.$namespace;
		static::$namespace  = $namespace;
		
		static::$db = \Mongo_Db::instance();

		// if a Ref was passed
		if ($ref)
		{
			self::$ref = $ref; 

			//query database for document's summary
			$summary = static::$db->get_where(static::$collection, array('ref'  => $ref), 1);

			// if there was a result
			if (count($summary) == 1)
			{
				$this->summary = $summary;
				$this->set('where', array(
					'_ref' => self::$ref
				));

				// cache active data
				self::$active = $summary[0]['revisions']['active'];

				// if just a document exists check - return true, no need for additional queries
				if ($check_exists)
				{
					return true;
				}
/*
				//query database for active document's data
				$data = static::$db
							->select(array(), array('_id'))
							->where(array(
								'_about.ref'            => self::$ref,
								'_about.revision' => self::$active
							))
							->get(static::$collection);

				$this->document   = $data;
*/
			}
			// collection doesn't exist
			else
			{
				throw new \TapiocaException(
					__('tapioca.document_not_found', array('ref' => self::$ref, 'collection' => self::$namespace))
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
	 * Gets the summaries of all documents's collection
	 *
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function all()
	{
		//query database for collections's summaries
		return static::$db
				->where(array(
					'summary' => array( '$exists' => true )
				))
				->order_by(array(
					'date.created' => 'desc'
				))
				->get(static::$collection);
	}

	/**
	 * Query Definition
	 * usage : $db->set('where', array('foo' => 'bar'));
	 * usage : $db->set(array('where' => array('foo' => 'bar'), 'select' => array('foo', 'bar'));
	 *
	 * @param   string|array   
	 * @param   string  
	 */	
	public function set($operator, $value = '')
	{	
		if(is_string($operator))
		{
			self::_set($operator, $value);
		}
		
		if(is_array($operator))
		{
			foreach($operator as $key => $value)
			{
				self::_set($key, $value);
			}
		}
	}
	
	private function _set($operator, $value)
	{		
		if(in_array($operator, self::$operators))
		{
			if(is_array($value))
			{
				$tmp_arr = $this->$operator;

				foreach($value as $key => $val)
				{
					$tmp_arr[$key] = $val;
				}

				$this->$operator = $tmp_arr;
			}
			else // string or int
			{
				$this->$operator = $value;
			}
		}
	}

	private function _unset($operator, $key)
	{
		$tmp_arr = $this->$operator;

		if(isset($tmp_arr[$key]))
		{
			unset($tmp_arr[$key]);

			$this->$operator = $tmp_arr;
		}
	}

	/**
	 * Gets the document
	 *
	 * @params  int Revision number 
	 * @return  array
	 * @throws  TapiocaException
	 */
	public function get($revision = null)
	{
		if(is_null(static::$collection))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		$this->set('where', array(
			'summary' => array( '$exists' => false ),
			'_about.active' => true
		));

		// get a specific revison
		if(!is_null($revision))
		{
			$this->_unset('where', '_about.active');
			$this->_unset('where', '_about.status');
			$this->set('where', array('_about.revision' => $revision));
		}
		else if(!is_null(self::$active))
		{
			$this->set('where', array('_about.revision' => self::$active));
		}

		$result = static::$db
			->select($this->select, array('_id'))
			->where($this->where)
			->order_by($this->sort)
			->get(static::$collection);

		if($result)
		{
			return $result;
		}
		
		if(self::$ref)
		{
			if(!is_null($revision))
			{
				throw new \TapiocaException(
					__('tapioca.document_revision_not_found', array('ref' => self::$ref, 'collection' => self::$namespace, 'revision' => $revision))
				);
			}
			else
			{
				throw new \TapiocaException(
					__('tapioca.document_not_found', array('ref' => self::$ref, 'collection' => self::$namespace))
				);
			}
		}
		// return no result
		return null;
	}

	/**
	*	--------------------------------------------------------------------------------
	*	Debug: LastQuery
	*	--------------------------------------------------------------------------------
	*
	*	Return Data about last query
	*/
	
	public function last_query()
	{
		return array(
			'select'	=> $this->select,
			'where'		=> $this->where,
			'limit'	    => $this->limit,
			'skip'		=> $this->skip,
			'sort'		=> $this->sort
		);
	}
	
}