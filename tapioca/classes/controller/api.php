<?php
/**
 * Tapioca: Schema Driven Data Engine 
 * Flexible CMS build on top of MongoDB, FuelPHP and Backbone.js
 *
 * @package   Tapioca
 * @version   v0.8
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/App
 */

class Controller_Api extends Controller_Rest
{
	protected static $granted = true;
	protected static $data    = array('message' => 'Method Not Implemented');
	protected static $status  = 501;
	protected static $user    = false;
	protected static $app     = null;
	protected static $apiKey  = false;
	protected static $token   = false;

	public function before()
	{
		parent::before();

		Config::load('rest', true);

		self::$apiKey = Input::get('key', false);
		self::$token  = Input::get('token', false);

		// if user is loggued to backoffice
		if( Tapioca::check() )
		{
			try
			{
				self::$user = Tapioca::user();
			}
			catch ( UserException $e )
			{
				Config::set('rest.auth', 'locked');
				self::error( $e->getMessage() );
			}
		}
		// if api key provided
		else if( self::$apiKey )
		{
			self::$user = static::$apiKey;
		}
		else
		{
			Config::set('rest.auth', 'locked');
			self::restricted();
		}

		// set default format
		$this->format = 'json';

	}

	protected static function signature()
	{
		$method = Input::method();

	}

	protected static function assignApp()
	{
		try
		{
			self::$app = Tapioca::app( array( 'slug' => static::$appslug ) );
		}
		catch ( \AuthException $e )
		{
			self::$granted = false;
			self::error( $e->getMessage() );

			return false;
		}

		return true;
	}

	// TODO: to remove
	// done in permission settings
	protected static function isInApp()
	{
		if( self::$granted )
		{
			// Check if user is a member of the app
			$userId = self::$user->get('id');
			$inApp  = self::$app->in_app( $userId );

			if( !$inApp )
			{
				self::restricted();
				return false;
			}

			return true;
		}
	}

	// TODO: to remove
	// done in permission settings
	protected static function isAppAdmin()
	{
		return static::$app->is_admin( static::$user->get('id') );
	}

	// TODO: to remove
	// done in permission settings
	protected static function isAdmin()
	{
		if( self::$user )
			return self::$user->is_admin();

		return false;
	}
	
	protected static function restricted()
	{
		Config::set('rest.auth', 'locked');

		self::$granted = false;
		self::$status  = 401;
		self::$data    = array(
			'message' => 'Access not allowed'
		);
	}

	protected static function error( $message, $status = 501, $debug = null )
	{
		Config::set('rest.auth', 'locked');
		
		if( !is_array( $message ) )
		{
			$message = array('error' => $message);
		}

		self::$data = $message;

		// Get validation error
		$rulesErrors = Tapioca::getFailedRules();

		if( count($rulesErrors) > 0 )
		{
			self::$data['rules'] = $rulesErrors;
		}

		if( !is_null( $debug ) )
		{
			self::$data['debug'] = $debug;
		}

		self::$status = $status;
		self::$granted = false;
	}

	protected static function deleteToken( $object = null, $id = null )
	{
		if( ! self::$token )
		{
			try
			{
				self::$data   = tapioca::getDeleteToken( $object , $id );
				self::$status = 200;

				// prevent next action
				return false;
			}
			catch (TapiocaException $e)
			{
				self::error( $e->getMessage() );

				// prevent next action
				return false;
			}
		}
		else 
		{
			try
			{
				Tapioca::checkDeleteToken( self::$token, $object, $id );

				return true;
			}
			catch (TapiocaException $e)
			{
				self::error( $e->getMessage() );

				// prevent next action
				return false;
			}
		}
	}

	// collection helper
	protected function dispatch(&$summary, &$schema, $values)
	{
		$arrSummary = Config::get('tapioca.collection.dispatch.summary');
		$arrData    = Config::get('tapioca.collection.dispatch.data');

		foreach($values as $key => $value)
		{
			if(in_array($key, $arrSummary))
			{
				$summary[$key] = $value;
			}

			if(in_array($key, $arrData))
			{
				$schema[$key] = $value;
			}
		}

		return;
	}

	// document helper
	protected function clean()
	{
		$model = Input::json(null, false);

		if( $model )
		{
			if(isset($model['_ref']))
			{
				unset($model['_ref']);
			}

			if(isset($model['_tapioca']))
			{
				unset($model['_tapioca']);
			}

			return $model;
		}
		
		static::$data   = array('error' => __('tapioca.missing_required_params'));
		static::$status = 500;

		return false;
	}

	public function after( $response )
	{
		$this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
		$this->response(self::$data, self::$status);

		return $this->response;
	}
}