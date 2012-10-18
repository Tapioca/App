<?php

class Controller_Api_App_Admin extends Controller_Api
{
	protected static $appslug;
	protected static $userId;
	protected static $member;  // working user

	public function before()
	{
		parent::before();

		static::$appslug = $this->param('appslug', false);
		static::$userId  = $this->param('userid', false);

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
			try
			{
				static::$app->add_admin( static::$userId );
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
				return;
			}

			static::$data   = static::$app->get();
			static::$status = 200;
		}
	}

	public function delete_index()
	{
		if( static::$granted )
		{
			if( ! static::deleteToken( 'app_admin', static::$userId ))
			{
				return;
			}

			try
			{
				static::$app->revoke_admin( static::$userId );
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
				return;
			}

			static::$data   = static::$app->get();
			static::$status = 200;
		}
	}
}