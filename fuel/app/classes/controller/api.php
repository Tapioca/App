<?php

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

		self::$apiKey = Input::get('apikey', false);
		self::$token  = Input::get('token', false);

		if ( !Tapioca::check() && !static::$apiKey )
		{
			self::restricted();
		}
		else
		{
			// TODO: add api key check
			try
			{
				self::$user = Tapioca::user();
			}
			catch ( UserException $e )
			{
				self::$granted = false;
				self::error( $e->getMessage() );
			}
		} // Auth

		// set default format
		$this->format = 'json';

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

	protected static function isAppAdmin()
	{
		return static::$app->is_admin( static::$user->get('id') );
	}

	protected static function restricted()
	{
		self::$granted = false;
		self::$status  = 401;
		self::$data    = array(
			'message' => 'Access not allowed'
		);
	}

	protected static function isAdmin()
	{
		if( self::$user )
			return self::$user->is_admin();

		return false;
	}

	protected static function error( $message, $status = 501, $debug = null )
	{
		if( !is_array( $message ) )
		{
			$message = array('error' => $message);
		}

		self::$data = $message;

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

	public function after( $response )
	{
		$this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
		$this->response(self::$data, self::$status);

		return $this->response;
	}
}