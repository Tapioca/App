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
	 * @var  string  App collection
	 */
	protected static $collection = '';

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
		static::$collection = strtolower(Config::get('tapioca.collections.apps'));

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
		$val   = current($query);
		$key   = key($query);

		//query database for app
		$app = static::$db->get_where(static::$collection, $query, 1);

		// if there was a result - update user
		if (count($app) == 1)
		{
			$this->app  = $app[0];
			$this->team   = $app[0]['team'];
			$this->admins = $app[0]['admins'];

			$this->app['locales_keys'] = array();

			foreach ($app[0]['locales'] as $locale)
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

	public static function getAll()
	{
		$apps = static::$db->hash(static::$collection, true);

		return $apps;
	}


	/**
	 * Return app's info.
	 *
	 * @param   string|array  App ID, can be an array of ID 
	 * @return  int|bool
	 */
	public function read($id)
	{
		static::$db->select(array(), array('_id', 'admins', 'name', 'slug'));

		if(is_array($id))
		{
			static::$db->where_in('id', $id);
		}
		else
		{
			static::$db->where(array('id' => $id));
		}

		return static::$db->get(static::$collection);
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
			throw new \AppException(__('tapioca.app_name_empty'));
		}

		$slug          = \Arr::get($app, 'slug', $app['name']);
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

		$app_id = uniqid();

		$app['id'] = $app_id;

		$result = static::$db->insert(static::$collection, $app);

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
		if ($field === null)
		{
			return array(
				'id'      => $this->app['id'],
				'name'    => $this->app['name'],
				'slug'    => $this->app['slug'],
				'admins'  => $this->app['admins'],
				'locales' => $this->app['locales'],
				'team'    => $this->app['team']
			);
		}
		// if field is an array - return requested fields
		else if (is_array($field))
		{
			$values = array();

			// loop through requested fields
			foreach ($field as $key)
			{
				// check to see if field exists in app
				if (array_key_exists($key, $this->app))
				{
					$values[$key] = $this->app[$key];
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
			// check to see if field exists in app
			if (array_key_exists($field, $this->app))
			{
				return $this->app[$field];
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

		// update name ?? check Slug migth be better ?
		if (array_key_exists('name', $fields) and $fields['name'] != $this->app['name'])
		{
			// make sure name does not already exist
			if (static::app_exists($fields['name']))
			{
				throw new \AppException(
					__('tapioca.app_already_exists', array('app' => $fields['name']))
				);
			}
			$update['name'] = $fields['name'];
			unset($fields['name']);
		}

		// update level
		if (array_key_exists('level', $fields))
		{
			$update['level'] = $fields['level'];
		}

		// update is_admin
		if (array_key_exists('is_admin', $fields))
		{
			$update['is_admin'] = $fields['is_admin'];
		}

		if (empty($update))
		{
			return true;
		}

		return static::$db
						->where(array('_id' => $this->app['_id']))
						->update(static::$collection, $update);
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
			throw new \AppException(__('tapioca.no_app_selected'));
		}

		$delete_app = self::$db
							->where(array('_id' => $this->app['_id']))
							->delete(static::$collection);

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
	 * @param   string|int  App name|App id
	 * @return  bool
	 */
	public static function app_exists($app)
	{
		$app_exists = static::$db
							->where(array('slug' => $app))
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
	public function in_app($id)
	{
		foreach ($this->team as $team)
		{
			if ($team['id'] == $id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Adds auser user to the app.
	 *
	 * @param   string User ID 
	 * @param   array  User's level right in app
	 * @return  bool
	 * @throws  AppException
	 */
	public function add_to_app($userId, $level = 0)
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
			throw new \AppException( $e->getMessage() );
		}

		$user_info = array(
				'id'       => $user->get('id'),
				'level'    => $level,
			);

		$update = array('$push' => array('team' => $user_info));

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			$this->team[] = $user_info;

			return true;
		}

		return false;
	}

	/**
	 * Removes this user from the app.
	 *
	 * @param   string|int  App ID or app name
	 * @return  bool
	 * @throws  AppException
	 */
	public function remove_from_app($email)
	{
		if ( ! $this->in_app($email))
		{
			throw new \AppException(
				__('tapioca.user_not_in_app', array('app' => $this->get('name')))
			);
		}

		try
		{
			$user = new User($email);
		}
		catch (UserNotFoundException $e)
		{
			throw new \AppException($e->getMessage());
		}

		$update = array('$pull' => array('team' => array('email' => $email)));

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			foreach ($this->team as $team)
			{
				if ($team['email'] == $email)
				{
					unset($team);
				}
			}

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
		$admins = $this->get('admins');
		
		return in_array($userId, $admins);
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
				$member['level'] = 100;
			}
		}

		$update = array(
						'$addToSet' => array('admins' => $userId),
						'$set' => array('team' => $this->team)
					);

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

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
	 * @param   string User _ID
	 * @return  bool
	 * @throws  AppException
	 */
	public function revoke_admin($id)
	{
		if ( ! $this->in_app($id))
		{
			throw new \AppException(
				__('tapioca.user_not_in_app', array('app' => $this->get('name')))
			);
		}

		try
		{
			$user = new User($id);
		}
		catch (UserNotFoundException $e)
		{
			throw new \AppException($e->getMessage());
		}

		$update = array('$pull' => array('admins' => $user->get('id')));

		$where = array('_id' => $this->app['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			/*
			if(!($id instanceof \MongoId))
			{
				$id = new \MongoId($id);
			}
			*/
			foreach ($this->admins as $admin)
			{
				if ($admin == $id)
				{
					unset($admin);
				}
			}

			return true;
		}

		return false;
	}

}

