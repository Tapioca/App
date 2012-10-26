<?php

class Controller_Api_App extends Controller_Api
{
	protected static $appslug;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		static::$appslug = $this->param('appslug', false);

		// if no appslug define and user not admin
		// => GET All && POST
		if( !static::$appslug && !static::isAdmin() )
		{
			static::restricted();
		}

		if( static::$appslug && !static::assignApp())
		{
			return;
		}

		// if not admin and not app's member
		// GET App details
		if( static::$appslug && ( !static::isAdmin() || !static::isInApp() ) )
		{
			static::restricted();
		}
	}

	public function get_index()
	{
		if( static::$granted )
		{
			if( static::$appslug )
			{
				static::$data   = static::$app->get();
				static::$status = 200;
			}
			else
			{
				$set = Input::get('set', null);

				static::$data   = App::getAll( $set );
				static::$status = 200;
			}
		}
	}

	public function post_index()
	{
		if( static::$granted )
		{
			// fixture
			// $fields = array(
			// 	'name'  => 'Group Test API v2',
			// 	'slug'  => 'gtapiv2',
			// );

			$fields = Input::json();

			if( isset( $fields['user'] ) )
			{
				$userId = $fields['user'];
				unset( $fields['user'] );
			}
			else
			{
				static::error( __('tapioca.missing_required_params') );
				return;
			}

			// make sure user exists
			try
			{
				$user = Tapioca::user( $userId );
			}
			catch (AuthException $e)
			{
				static::error( $e->getMessage() );
				return;
			}

			// create an app
			try
			{
				$appId = Tapioca::app()->create($fields);
				
				if( $appId )
				{
					static::$app = Tapioca::app($appId);

					// Create app's admin
					static::$app->add_to_app( $userId, 100 );
					static::$app->add_admin( $userId );

					$user->add_to_app( $appId );

					static::$data   = static::$app->get();
					static::$status = 200;
				}
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
			}
		}
	}

	public function put_index()
	{
		if( static::$granted && static::$appslug && ( static::isAdmin() || static::isAppAdmin() ) )
		{
			try
			{
				// fixture
				// $fields = array(
				// 	'name'  => 'Les Bouffes du Nord',
				// 	'slug'  => 'gtapiv2test',
				// );

				$fields = Input::json();

				$action = static::$app->update( $fields );
				
				if( $action )
				{
					static::$data   = static::$app->get();
					static::$status = 200;
				}
				else
				{
					static::error( __('tapioca.internal_server_error'), 500 );
				}
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
			}
		}
	}

	public function delete_index()
	{
		if( static::$granted && static::$appslug && ( static::isAdmin() || static::isAppAdmin() ) )
		{
			if( ! static::deleteToken( 'app', static::$appslug ))
			{
				return;
			}

			try
			{
				$action = static::$app->delete();
				
				if( $action )
				{
					static::$data   = array( 'message' => 'app deleted' );
					static::$status = 200;
				}
				else
				{
					static::error( __('tapioca.internal_server_error'), 500 );
				}
			}
			catch (AppException $e)
			{
				static::error( $e->getMessage() );
			}
		}
	}
}