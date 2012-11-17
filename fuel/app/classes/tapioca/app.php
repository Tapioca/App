<?php

namespace Tapioca;

use Config;
use Mongo_Db;
use FuelException;

class AppException extends FuelException {}
class AppNotFoundException extends AppException {}

class App
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  string  MongoDb collection's name
	 */
	protected static $dbCollectionName = '';

	/**
	 * @var  array  App array
	 */
	protected $app = array();

	/**
	 * @var  array  App's team array
	 */
	protected $team = array();

	/**
	 * @var  array  App's admins array
	 */
	protected $admins = array();

	/**
	 * @var  array  App's locales list
	 */
	protected $locales = null;

	/**
	 * @var  array  App's default locale
	 */
	protected $locale_default = null;


	/**
	 * Gets the collection names
	 */
	public static function _init()
	{
		static::$dbCollectionName = strtolower(Config::get('tapioca.collections.apps'));

		static::$db = Mongo_Db::instance();
	}

	/**
	 * Checks if the Field is set or not.
	 *
	 * @param   string  Field name
	 * @return  bool
	 */
	public function __isset($field)
	{
		return array_key_exists($field, $this->app);
	}

	/**
	 * Gets a field value of the app
	 *
	 * @param   string  Field name
	 * @return  mixed
	 * @throws  AppException
	 */
	public function __get($field)
	{
		return $this->get($field);
	}

	/**
	 * Copy app properties and remove
	 * privates data for public display
	 *
	 * @return  App object
	 */
	public function __clone()
	{
		unset( $this->app['_id'] );
		unset( $this->app['locales_keys'] );
		unset( $this->app['locale_default'] );
	}


	/**
	 * Gets all the app info.
	 *
	 * @param   string|int  App id or name
	 * @return  void
	 */
	public function __construct($id = null)
	{
		if ($id === null)
		{
			return;
		}

		$query = (is_array($id)) ? $id : array('id' => $id);
			
		$this->load( $query );
	}

	private function load( $query )
	{
		//query database for app
		$app = static::$db->get_where(static::$dbCollectionName, $query, 1);
		$val = current($query);

		if (count($app) == 1)
		{
			$app          = $app[0];

			$this->app    = $app;
			$this->team   = $app['team'];
			$this->admins = $app['admins'];

			$this->app['locales_keys'] = array();

			foreach ($app['locales'] as $locale)
			{
				$this->app['locales_keys'][] = $locale['key'];

				if(isset($locale['default']) && $locale['default'] == true)
				{
					$this->app['locale_default'] = $locale['key'];
				}
			}
		}
		// app doesn't exist
		else
		{
			throw new \AppNotFoundException(
				__('tapioca.app_not_found', array('app' => $val))
			);
		}
	}

	public static function getAll( $set = null)
	{
		if( !is_null( $set ))
		{
			static::$db->where_in( 'slug', explode(';', $set) );
		}

		$apps = static::$db->hash( static::$dbCollectionName, true );

		return $apps;
	}

	/**
	 * Creates the given app.
	 *
	 * @param   array  App info
	 * @return  int|bool
	 */
	public function create(array $app)
	{
		if ( ! array_key_exists('name', $app) || $app['name'] == '')
		{
			throw new \AppException( __('tapioca.app_name_empty') );
		}

		$slug        = \Arr::get($app, 'slug', $app['name']);
		$app['slug'] = \Inflector::friendly_title($slug, '-', true);

		\Config::load('slug', true);

		if(in_array($app['slug'], \Config::get('slug.reserved')))
		{
			throw new \AppException(
				__('tapioca.app_slug_invalid', array('app' => $app['slug']))
			);		
		}

		if (static::app_exists($app['slug']))
		{
			throw new \AppException(
				__('tapioca.app_already_exists', array('app' => $app['slug']))
			);
		}

		if ( ! array_key_exists('team', $app))
		{
			$app['team'] = array();
		}

		if ( ! array_key_exists('admins', $app))
		{
			$app['admins'] = array();
		}

		if ( ! array_key_exists('locales', $app))
		{
			$app['locales']   = array();
			$app['locales'][] = \Config::get('tapioca.locales.default');
		}

		if ( ! array_key_exists('library', $app))
		{
			$app['library'] = array(
				'presets'      => array(),
				'files'        => array(),
				'extwhitelist' => \Config::get('tapioca.upload.ext_whitelist')
			);

			$fileTypes = \Config::get('tapioca.file_types');

			foreach ($fileTypes as $key => $value)
			{
				$app['library']['files'][$key] = 0;
			}

		}

		$app_id = uniqid();

		$app['id'] = $app_id;

		$result = static::$db->insert(static::$dbCollectionName, $app);

		if(count($result) > 0)
		{
			return $app_id;
		}

		return false;
	}

	/**
	 * Gets a given field (or array of fields).
	 *
	 * @param   string|array  Field(s) to get
	 * @return  mixed
	 * @throws  AppException
	 */
	public function get($field = null)
	{
		// make sure a app id is set
		if (empty($this->app['_id']))
		{
			throw new \AppException(__('tapioca.no_app_selected'));
		}

		// if no fields were passed - return entire app
		if ( is_null( $field ) )
		{
			$public = clone $this;

			return $public->app;
		}
		// if field is an array - return requested fields
		else if (is_array($field))
		{
			$values = array();

			// loop through requested fields
			foreach ($field as $key)
			{
				// check to see if field exists in app
				$val = \Arr::get($this->app, $key, '__MISSING_KEY__');
				if ($val !== '__MISSING_KEY__')
				{
					$values[$key] = $val;
				}
				else
				{
					throw new \AppException(
						__('tapioca.not_found_in_app_object', array('field' => $key))
					);
				}
			}

			return $values;
		}
		// if single field was passed - return its value
		else
		{
			// check to see if field exists in user
			$val = \Arr::get($this->app, $field, '__MISSING_KEY__');
			if ($val !== '__MISSING_KEY__')
			{
				return $val;
			}

			throw new \AppException(
				__('tapioca.not_found_in_app_object', array('field' => $field))
			);
		}
	}


	/**
	 * Update the given app
	 *
	 * @param   array  fields to be updated
	 * @return  bool
	 * @throws  AppException
	 */
	public function update(array $fields)
	{
		// make sure a app id is set
		if (empty($this->app['_id']))
		{
			throw new \AppException(__('tapioca.no_app_selected'));
		}

		// init the update array
		$update = array();

		// update name
		if (array_key_exists('name', $fields) and $fields['name'] != $this->app['name'])
		{
			$update['name'] = $fields['name'];
			unset($fields['name']);
		}

		// update locales
		if (array_key_exists('locales', $fields))
		{
			$update['locales'] = $fields['locales'];
		}

		// update extension whitelist
		if (array_key_exists('extwhitelist', $fields['library']))
		{
			$update['library'] = $this->get('library');
			
			$update['library']['extwhitelist'] = $fields['library']['extwhitelist'];
		}

		if (empty($update))
		{
			return true;
		}

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
						->where( $where )
						->update( static::$dbCollectionName, $update );

		if( $query )
		{
			$this->load( $where );
			return true;
		}

		return false;
	}


	/**
	 * Delete's the current app.
	 *
	 * @return  bool
	 * @throws  AppException
	 */
	public function delete()
	{
		// make sure a user id is set
		if (empty($this->app['id']))
		{
			throw new \AppException( __('tapioca.no_app_selected') );
		}

		foreach ($this->team as $team)
		{
			try
			{
				Tapioca::user( $team['id'] )->remove_from_app( $this->get('slug'), $this->get('name') );
			}
			catch( UserException $e )
			{
				throw new \AppException( $e->getMessage() );
			}
		}

		$delete_app = static::$db
							->where( array('_id' => $this->app['_id']) )
							->delete(static::$dbCollectionName);

		if($delete_app )
		{
			// update user to null
			$this->app = array();

			return true;
		}
		return false;
	}

	/**
	 * Checks if the app exists
	 *
	 * @param   string  App slug
	 * @return  bool
	 */
	public static function app_exists( $appslug )
	{
		$app_exists = static::$db
							->where(array('slug' => $appslug))
							->limit(1)
							->count(Config::get('tapioca.collections.apps'));

		return (bool) $app_exists;
	}

	/*
	 * Checks if the current user is part of the given app.
	 *
	 * @param   string user ID
	 * @return  bool
	 */
	public function in_app($id, $strict = false)
	{
		foreach ($this->team as $team)
		{
			if ($team['id'] == $id)
			{
				return ( $strict ? true : ( $team['role'] != '_REVOKED_ACCESS_' ) );
			}
		}

		return false;
	}

	/**
	 * Adds auser user to the app.
	 *
	 * @param   string User ID 
	 * @param   string  User role in app
	 * @return  bool
	 * @throws  AppException
	 */
	public function add_to_app($userId, $role = null)
	{
		if ($this->in_app($userId))
		{
			throw new \AppException(
				__('tapioca.user_already_in_app', array('app' => $this->get('name')))
			);
		}

		try
		{
			$user = new User($userId);
		}
		catch (UserNotFoundException $e)
		{
			throw new \AppException($e->getMessage());
		}

		$roles = Config::get('tapioca.roles');

		if( is_null( $role ) || !in_array($role, $roles))
		{
			$role  = end($roles);
		}

		if ( $this->in_app($userId, true) )
		{
			$query = $this->user_role( $userId, $role );
		}
		else
		{
			$user_info = array(
					'id'   => $userId,
					'role' => $role,
				);

			$update = array('$push' => array('team' => $user_info));			

			$where = array('_id' => $this->app['_id']);

			$query = static::$db
						->where($where)
						->update(static::$dbCollectionName, $update, array(), true);

			$this->team[] = $user_info;
		}

		if($query)
		{
			return true;
		}

		return false;
	}

	/**
	 * Removes this user from the app.
	 *
	 * @param   string  User Id
	 * @return  bool
	 * @throws  AppException
	 */
	public function remove_from_app( $userId )
	{
		if ( ! $this->in_app( $userId ))
		{
			throw new \AppException(
				__('tapioca.user_not_in_app', array('app' => $this->get('name')))
			);
		}

		try
		{
			$user = new User( $userId );
		}
		catch (UserNotFoundException $e)
		{
			throw new \AppException($e->getMessage());
		}

		// $update = array('$pull' => array('team' => array('id' => $userId) ) );

		foreach ($this->team as $key => $row)
		{
			if ($row['id'] == $userId)
			{
				$this->team[ $key ]['role'] = '_REVOKED_ACCESS_';
				break;
			}
		}

		$update = array('$set' => array('team' => $this->team ) );

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$dbCollectionName, $update, array(), true);


		if($query)
		{
			$this->load( $where );

			return true;
		}

		return false;
	}

	/**
	 * Set user level
	 *
	 * @param   string User ID
	 * @param   string User role
	 * @return  bool
	 * @throws  AppException
	 */
	public function user_role( $userId, $role = null )
	{
		if ( ! $this->in_app( $userId, true ))
		{
			throw new \AppException(
				__('tapioca.user_not_in_app', array('app' => $this->get('name')))
			);
		}
		
		try
		{
			$user = new User($userId);
		}
		catch (UserNotFoundException $e)
		{
			throw new \AppException($e->getMessage());
		}

		$roles = Config::get('tapioca.roles');

		if( is_null( $role ) || !in_array($role, $roles))
		{
			$role  = end($roles);
		}

		foreach ($this->team as &$team)
		{
			if ($team['id'] == $userId)
			{
				$team['role'] = $role;
			}
		}

		$update = array( 'team' => $this->team );

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$dbCollectionName, $update);

		if($query)
		{
			$this->load( $where );

			return true;
		}

		return false;
	}

	/**
	 * Checks if the user is admin of the current app.
	 *
	 * @param   string  User ID
	 * @return  bool
	 */
	public function is_admin( $userId )
	{
		foreach ($this->team as $team)
		{
			if ($team['id'] == $userId)
			{
				return ($team['role'] == 'admin');
			}
		}
		return false;

		// $admins = $this->get('admins');
		
		// return in_array($userId, $admins);
	}

	/**
	 * Grante user as admin for the app.
	 *
	 * @param   string User ID
	 * @return  bool
	 * @throws  AppException
	 */
	public function add_admin( $userId )
	{
		if ( !$this->in_app( $userId ) )
		{
			throw new \AppException(
				__('tapioca.user_not_in_app', array('app' => $this->get('name')))
			);
		}

		if( $this->is_admin( $userId) )
		{
			return true;
		}

		try
		{
			$user = new User( $userId );
		}
		catch (UserNotFoundException $e)
		{
			throw new \AppException( $e->getMessage() );
		}

		foreach($this->team as &$member)
		{
			if($member['id'] == $userId)
			{
				$member['role'] = 'admin';
			}
		}

		$update = array(
						// '$addToSet' => array('admins' => $userId),
						'$set' => array('team' => $this->team)
					);

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$dbCollectionName, $update, array(), true);

		if($query)
		{		
			$this->admins[] = $userId;

			return true;
		}
		
		return false;
	}

	/**
	 * Revoke user as admin for the app.
	 *
	 * @param   string User Id
	 * @return  bool
	 * @throws  AppException
	 */
	public function revoke_admin( $userId )
	{
		if ( ! $this->in_app( $userId ))
		{
			throw new \AppException(
				__('tapioca.user_not_in_app', array('app' => $this->get('name')))
			);
		}

		if( !$this->is_admin( $userId) )
		{
			return true;
		}

		try
		{
			$user = new User( $userId );
		}
		catch (UserNotFoundException $e)
		{
			throw new \AppException($e->getMessage());
		}

		foreach ($this->admins as $key => $row)
		{
			if ($row == $userId)
			{
				unset( $this->admins[ $key ] );
			}
		}

		$update = array(
			'$pull' => array('admins' => $userId ),
			'$set'  => array('team' => $this->team)
		);

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$dbCollectionName, $update, array(), true);

		if($query)
		{
			return true;
		}

		return false;
	}


	/**
	 * Increment/decrement total files per categories
	 *
	 * @param   string  category
	 * @param   int   Increment|Decrement
	 * @return  void
	 */
	public function inc_library($category, $direction = 1)
	{
		$ret = static::$db->command(
					array('findandmodify' => static::$dbCollectionName,
						  'query'         => array('_id' => $this->app['_id']),
						  'update'        => array('$inc' => array('library.files.'.$category => (int) $direction)),
						  'new'           => true
					)
				);

		return (bool) ($ret['ok'] == 1);
	}

}

