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
	 * @var  int  last revison 
	 */
	protected static $last_revision = null;

	/**
	 * @var  array  selected Document
	 */
	protected $document = null;

	/**
	 * @var  array  selected Document's summary
	 */
	protected $summary = null;

	/**
	 * @var  array  document validation errors
	 */
	protected $errors = array();

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
			$summary = static::$db->select(array(), array('_id'))->get_where(static::$collection, array('ref'  => $ref), 1);

			// if there was a result
			if (count($summary) == 1)
			{
				$this->summary = $summary[0];
				$this->set('where', array(
					'_ref' => self::$ref
				));

				// cache data
				self::$active        = $this->summary['revisions']['active'];
				self::$last_revision = $this->summary['revisions']['total'];

				// if just a document exists check - return true, no need for additional queries
				if ($check_exists)
				{
					return true;
				}
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
	 *	Query database for active document's data
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
	 * API facade for create or update a document
	 *
	 * @params  array document data
	 * @return  array user data
	 * @throws  TapiocaException
	 */
	public function save($appid, array $document, $user)
	{
		if(is_null(static::$collection))
		{
			throw new \TapiocaException(__('tapioca.no_collection_selected'));
		}

		// Get Collection Definiton
		$collection      = Tapioca::collection($appid, self::$namespace); 
		$collection_data = $collection->data();

		// Set document summary
		try
		{
			$summary = $this->set_summary($collection_data['summary'], $document);
		}
		catch (TapiocaDocumentException $e)
		{
			throw new \TapiocaException( $e->getMessage() );
		}

		// Test document rules
		if(isset($collection_data['rules']))
		{
			if(!$this->test_rules($collection_data['rules'], $document))
			{
				\Debug::show($this->errors);
				exit;
				throw new \TapiocaException( 'fuck' );
			}
		}

		// new document
		if(is_null(static::$ref))
		{
			$this->create($document, $summary, $user);
		}
		else // update Document
		{
			$this->update($document, $summary, $user);
		}
	}

	private function create($document, $summary, $user)
	{
		$date = new \MongoDate();

		$ref = uniqid();

		$data = array(
			'_ref' => $ref,
			'_about' => array(
				'revision' => (int) 1,
				'status' => (int) 1,
				'active' => (bool) true,
				'user' => $user,
			)
		) + $document;

		$summary = array(
			'ref' => $ref,
			'summary' => (bool) true,
			'date' => array(
				'created' => $date
			),
			'revisions' => array(
				'total' => (int) 1,
				'active' => (int) 1,
				'list' => array(
					array(
						'revison' => 1,
						'date' => $date,
						'status' => (int) 1,
						'user' => $user,
					)
				)
			)
		) + $summary;

		$new_data = static::$db->insert(static::$collection, $data);

		if($new_data)
		{
			$new_summary = static::$db->insert(static::$collection, $summary);

			if($new_summary)
			{
				$this->summary = $summary;
				self::$ref = $ref;
				self::$active = 1;
			}
		}
	}

	private function update($document, $summary, $user)
	{
		$date = new \MongoDate();
		++self::$last_revision;
		
		$is_active = $this->set_active();

		$data = array(
			'_ref' => self::$ref,
			'_about' => array(
				'revision' => (int) self::$last_revision,
				'status' => (int) 1,
				'active' => (bool) $is_active,
				'user' => $user,
			)
		) + $document;

		++$this->summary['revisions']['total'];

		$this->summary['data']                = $summary['data'];
		$this->summary['date']['updated']     = $date;
		$this->summary['revisions']['list'][] = array(
													'revison' => (int) self::$last_revision,
													'date' => $date,
													'status' => (int) 1,
													'user' => $user,
												);
		// update active revision
		if($is_active)
		{
			$update_active = static::$db
								->where(array('_ref' => self::$ref))
								->update_all(static::$collection, array('_about.active' => (bool) false));

			$this->summary['revisions']['active'] = (int) self::$last_revision;
		}

		$new_data = static::$db->insert(static::$collection, $data);

		if($new_data)
		{
			$new_summary = static::$db
								->where(array(
									'ref'       => self::$ref,
									'summary'   => (bool) true
								))
								->update(static::$collection, $this->summary);
		}
	}

	/**
	 * Extract document summary 
	 *
	 * @params  array collection summary definition
	 * @params  array document data
	 * @return  array document summary
	 * @throws  TapiocaException
	 */
	private function set_summary($structure, $document)
	{
		$summary = array('data' => array());

		foreach($structure as $key => $v)
		{
			$value = \Arr::get($document, $key, '__DOC_MISSING_VALUE__');

			if($value != '__DOC_MISSING_VALUE__')
			{
				$summary['data'][$key] = $value;
			}
			else
			{
				throw new \TapiocaDocumentException(
					__('tapioca.document_column_is_empty', array('column' => $v))
				);
			}
		}

		return $summary;
	}

	/**
	 * Test each rules in the current document 
	 *
	 * @params  array collection rules definition
	 * @params  array document data
	 * @return  bool
	 */
	private function test_rules($rules_list, $document)
	{
		foreach($rules_list as $field => $rules)
		{
			$value = \Arr::get($document, $field, null);
			$args  = array($value);
			
			foreach($rules as $rule)
			{
				// Strip the parameter (if exists) from the rule
				// Rules can contain a parameter: max_length[5]
				$param = false;
				
				if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
				{
					$rule	= $match[1];
					$param	= explode('|', $match[2]);
					$args	= array_merge($args, $param);
				}
				
				$valid = call_user_func_array(array(__NAMESPACE__ .'\Rules', $rule), $args);
				
				if(!$valid)
				{
					$obj = new \stdClass;
					$obj->rule = $rule;
					$obj->args = array_merge(array('$item[id]'), (array) $param);
					
					$this->errors[] = $obj;
					
					return false;
				}
			}
		}
		
		return true;
	}

	/**
	 * Check if new revision has higher status than the others
	 * If we found a status 100 (published), return false
	 *
	 * @return  bool
	 */
	private function set_active()
	{
		$higher = 1;
		
		foreach ($this->summary['revisions']['list'] as $revision)
		{
			$higher = ($revision['status'] > $higher) ? $revision['status'] : $higher;

			if($revision['status'] == 100)
			{
				return false;
			}
		}

		if($higher > 1)
		{
			return false;
		}

		return true;
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