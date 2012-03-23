<?php

/*
use Config;
use FuelException;
use Cookie;
use Session;
*/

class AuthException extends FuelException {}

class Auth
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

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
		Config::load('auth', true);

		// set static vars for later use
		static::$suspend = trim(Config::get('auth.limit.enabled'));


		// db_instance check
		static::$db = \Mongo_Db::instance();
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
				static::$user_cache[$id] = new \Model\User($id);
				return static::$user_cache[$id];
			}
			// if session exists - default to user session
			else if(static::check())
			{
				$user_id = Session::get(Config::get('auth.session.user'));
				static::$current_user = new \Model\User($user_id);
				return static::$current_user;
			}
		}
		catch (\Model\UserNotFoundException $e)
		{
			throw new \AuthException($e->getMessage());
		}

		// else return empty user
		return new \Model\User();
	}
}