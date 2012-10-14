<?php

class Controller_Api_App_User extends Controller_Api
{
	protected static $appslug;
	protected static $userId;
	protected static $member;  // working user

	public function before()
	{
		parent::before();

		static::$appslug = $this->param('appslug', false);
		static::$userId  = $this->param('userid', false);

		try
		{
			static::$member = Tapioca::user( static::$userId );
		}
		catch (AuthException $e)
		{
			static::error( $e->getMessage() );
			return;
		}

		if( static::$appslug && !static::assignApp())
		{
			return;
		}

		// if not admin and not in member of the app
		if( static::$appslug && ( !static::isAdmin() || !static::isAppAdmin() ) )
		{
			static::restricted();
		}
	}

	public function post_index()
	{
		if( static::$granted )
		{
			// fixtures
			$level  = 100;

			try
			{
				static::$app->add_to_app( static::$userId, $level );
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
				return;
			}

			try
			{
				static::$member->add_to_app( static::$app->get('id') );
			}
			catch (UserException $e)
			{
				static::error( $e->getMessage() );
				return;
			}

			static::$data   = static::$app->get();
			static::$status = 200;
		}
	}

	public function put_index()
	{
		if( static::$granted )
		{
			// fixtures
			$level  = 50;

			try
			{
				// user Id provided by JSON
				static::$app->user_level( static::$userId, $level );

				static::$data   = static::$app->get();
				static::$status = 200;
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
			}
		}
	}

	public function delete_index()
	{
		if( static::$granted )
		{
			if( ! static::$token )
			{
				try
				{
					static::$data   = tapioca::getDeleteToken( 'app_user', static::$userId );
					static::$status = 200;
					return;
				}
				catch (TapiocaException $e)
				{
					static::error( $e->getMessage() );
					return;
				}
			}
			else 
			{
				try
				{
					Tapioca::checkDeleteToken( static::$token );
				}
				catch (TapiocaException $e)
				{
					static::error( $e->getMessage() );
					return;
				}
			}

			try
			{
				static::$app->remove_from_app( static::$userId );
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
				return;
			}

			try
			{
				static::$member->remove_from_app( static::$app->get('id') );
			}
			catch (UserException $e)
			{
				static::error( $e->getMessage() );
				return;
			}

			static::$data   = static::$app->get();
			static::$status = 200;
		}
	}
}