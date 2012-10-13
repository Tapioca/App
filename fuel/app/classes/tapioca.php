<?php

namespace Tapioca;

use FuelException;
use Config;
use Lang;
use Session;
use Cookie;

class TapiocaException extends \FuelException {}

class Tapioca 
{
	/**
	 * @var  string  Database instance
	 */
	private static $db = null;

	/**
	 * @var  bool  Whether suspension feature should be used or not
	 */
	protected static $suspend = null;

	/**
	 * @var  Auth_Attempts  Holds the Auth_Attempts object
	 */
	protected static $attempts = null;

	/**
	 * @var  array  Caches all users accessed
	 */
	protected static $user_cache = array();

	/**
	 * @var  object  Caches the current logged in user object
	 */
	protected static $current_user = null;

	/**
	 * Prevent instantiation
	 */
	final private function __construct() {}

	/**
	 * Run when class is loaded
	 *
	 * @return  void
	 */
	public static function _init()
	{
		// load config
		Config::load('tapioca', true);
		Lang::load('tapioca', 'tapioca');

		// set static vars for later use
		static::$suspend = trim( Config::get('tapioca.limit.enabled') );
	}

	/**
	 * Called to init Lang in UI
	 *
	 * @return  void
	 */
	public static function base()
	{
	}

	/**
	 * @param   string app id
	 * @param   MongoId|string Collection id.
	 * @throws  TapiocaException
	 * @return  Collection
	 */
	public static function collection($appid, $id = null)
	{
		try
		{
			return new \Collection($appid, $id);
		}
		catch (TapiocaCollectionException $e)
		{
			throw new \TapiocaException($e->getMessage());
		}

		//\Debug::dump('Tapioca collection call');
		//
	}

	/**
	 * @param   string app slug
	 * @param   string collection namespace.
	 * @param   string document reference.
	 * @param   string document locale.
	 * @throws  TapiocaException
	 * @return  Document
	 */
	public static function document($app_slug, $namespace, $ref = null, $locale = null)
	{
		try
		{
			return new \Document($app_slug, $namespace, $ref, $locale);
		}
		catch (TapiocaDocumentException $e)
		{
			throw new \TapiocaException($e->getMessage());
		}

		//\Debug::dump('Tapioca collection call');
		//
	}

	/**
	 * @param   string app slug
	 * @param   string document reference.
	 * @throws  TapiocaException
	 * @return  Document
	 */
	public static function file($app_slug, $filename = null)
	{
		try
		{
			return new \Files($app_slug, $filename);
		}
		catch (TapiocaFileException $e)
		{
			throw new \TapiocaException($e->getMessage());
		}
	}

	public static function set_status($status = array())
	{
		$defaults = Config::get('tapioca.status');

		if(count($status) > 1)
		{
			return array_merge($defaults, $status);
		}

		return $defaults;
	}

	/**
	 * @return  Bool
	 */
	public static function check_install()
	{
		return \Auth::user()->admin_set();
	}

	/**
	 * Get's either the currently logged in user or the specified user by id or Login
	 * Column value.
	 *
	 * @param   int|string  User id or Login Column value to find.
	 * @throws  AuthException
	 * @return  User
	 */
	public static function user($id = null, $recache = false)
	{
		if ( $id === 'all' )
		{
			$list = new User();
			return $list->getAll();
		}

		if ($id === null and $recache === false and static::$current_user !== null)
		{
			return static::$current_user;
		}
		elseif ($id !== null and $recache === false and isset(static::$user_cache[$id]))
		{
			return static::$user_cache[$id];
		}

		try
		{
			if ($id)
			{
				static::$user_cache[$id] = new User($id);
				return static::$user_cache[$id];
			}
			// if session exists - default to user session
			else if(static::check())
			{
				$user_id = Session::get(Config::get('tapioca.session.user'));
				static::$current_user = new User($user_id);
				return static::$current_user;
			}
		}
		catch (UserNotFoundException $e)
		{
			throw new \AuthException($e->getMessage());
		}

		// else return empty user
		return new User();
	}


	/**
	 * Checks if the current user is logged in.
	 *
	 * @return  bool
	 */
	public static function check()
	{
		// get session
		$user_id = Session::get(Config::get('tapioca.session.user'));
		
		// invalid session values - kill the user session
		if ($user_id === null)
		{
			// if they are not logged in - check for cookie and log them in
			if (static::is_remembered())
			{
				return true;
			}
			//else log out
			static::logout();

			return false;
		}

		return true;
	}

	/**
	 * Logs the current user out.  Also invalidates the Remember Me setting.
	 *
	 * @return  void
	 */
	public static function logout()
	{
		Cookie::delete(Config::get('tapioca.remember_me.cookie_name'));
		Session::delete(Config::get('tapioca.session.user'));
		Session::delete(Config::get('tapioca.session.provider'));
	}

	/**
	 * Remember User Login
	 *
	 * @param int
	 */
	protected static function remember($login_column)
	{
		// generate random string for cookie password
		$cookie_pass = \Str::random('alnum', 24);

		// create and encode string
		$cookie_string = base64_encode($login_column.':'.$cookie_pass);

		// set cookie
		Cookie::set(
			Config::get('tapioca.remember_me.cookie_name'),
			$cookie_string,
			Config::get('tapioca.remember_me.expire')
		);

		return $cookie_pass;
	}

	/**
	 * Check if remember me is set and valid
	 */
	protected static function is_remembered()
	{
		$encoded_val = Cookie::get(Config::get('tapioca.remember_me.cookie_name'));

		if ($encoded_val)
		{
			$val = base64_decode($encoded_val);
			list($login_column, $hash) = explode(':', $val);

			// if user is validated
			if ($user = static::validate_user($login_column, $hash, 'remember_me'))
			{
				// update last login
				$user->update(array(
					'last_login' => new \MongoDate()
				));

				// set session vars
				Session::set(Config::get('tapioca.session.user'), $user->get('id'));
				Session::set(Config::get('tapioca.session.provider'), 'Tapioca');

				return true;
			}
			else
			{
				static::logout();

				return false;
			}
		}

		return false;
	}



}