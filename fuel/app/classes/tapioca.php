<?php

namespace Tapioca;

use FuelException;
use Config;
use Lang;
use Session;
use Cookie;

class TapiocaException extends FuelException {}
class AuthException extends FuelException {}

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
		return static::user()->admin_set();
	}

	/**
	 * Get's either the currently logged in user's app object or the
	 * specified app by id or name.
	 *
	 * @param   int|string  App id or or name
	 * @return  Group
	 */
	public static function app($id = null)
	{
		try
		{
			if ($id)
			{
				return new App($id);
			}

			return new App();
		}
		catch (AppNotFoundException $e)
		{
			throw new AuthException($e->getMessage());
		}
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
	 * Gets the Attempts object
	 *
	 * @return  Attempts
	 */
	 public static function attempts($login_id = null, $ip_address = null)
	 {
	 	return new Attempts($login_id, $ip_address);
	 }

	/**
	 * Attempt to log a user in.
	 *
	 * @param   string  Login column value
	 * @param   string  Password entered
	 * @param   bool    Whether to remember the user or not
	 * @return  bool
	 * @throws  MontryAuthException
	 */
	public static function login($login_column_value, $password, $remember = false)
	{
		// log the user out if they hit the login page
		static::logout();

		// get login attempts
		if (static::$suspend)
		{
			$attempts = static::attempts($login_column_value, \Input::real_ip());

			// if attempts > limit - suspend the login/ip combo
			if ($attempts->get() >= $attempts->get_limit())
			{
				try
				{
					$attempts->suspend();
				}
				catch(UserSuspendedException $e)
				{
					throw new \AuthException($e->getMessage());
				}
			}
		}

		// make sure vars have values
		if (empty($login_column_value) or empty($password))
		{
			return false;
		}

		// if user is validated
		if ($user = static::validate_user($login_column_value, $password, 'password'))
		{
			if (static::$suspend)
			{
				// clear attempts for login since they got in
				$attempts->clear();
			}

			// set update array
			$update = array();

			// if they wish to be remembers, set the cookie and get the hash
			if ($remember)
			{
				$update['remember_me'] = static::remember($login_column_value);
			}

			// if there is a password reset hash and user logs in - remove the password reset
			if ($user->get('password_reset_hash'))
			{
				$update['password_reset_hash'] = '';
				$update['temp_password'] = '';
			}

			$update['last_login'] = new \MongoDate();
			$update['ip_address'] = \Input::real_ip();

			// update user
			if (count($update))
			{
				$user->update($update, false);
			}

			// set session vars
			Session::set(Config::get('tapioca.session.user'), $user->get('id'));
			Session::set(Config::get('tapioca.session.provider'), 'Tapioca');

			return true;
		}

		return false;
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
		Cookie::delete( Config::get('tapioca.remember_me.cookie_name') );

		Session::delete( Config::get('tapioca.session.user') );
		Session::delete( Config::get('tapioca.session.provider') );
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


	/**
	 * Validates a Login and Password.  This takes a password type so it can be
	 * used to validate password reset hashes as well.
	 *
	 * @param   string  Login column value
	 * @param   string  Password to validate with
	 * @param   string  Field name (password type)
	 * @return  bool|User
	 */
	protected static function validate_user($login_column_value, $password, $field)
	{
		// get user
		$user = static::user($login_column_value);

		// check activation status
		if ($user->activated != 1 and $field != 'activation_hash')
		{
			throw new \AuthException('account_not_activated');
		}

		// check user status
		if ($user->status != 1)
		{
			throw new \AuthException('account_is_disabled');
		}

		// check password
		if ( ! $user->check_password($password, $field))
		{
			if (static::$suspend and ($field == 'password' or $field == 'password_reset_hash'))
			{
				static::attempts($login_column_value, \Input::real_ip())->add();
			}
			return false;
		}

		return $user;
	}

}