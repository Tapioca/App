<?php

class Controller_Api_App extends Controller_Api
{
	protected static $appslug;
	protected static $isAllowed;

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

		// if not admin and not in member of the app
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
				static::$data   = App::getAll();
				static::$status = 200;
			}
		}
	}

	public function post_index()
	{
		if( static::$granted )
		{
			// fixture
			$fields = array(
				'name'  => 'Group Test API v2',
				'slug'  => 'gtapiv2',
			);

			// user Id provided by form
			try
			{
				$user = Tapioca::user( '5079459507a7d' );
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
				
				if($appId)
				{
					static::$app = Tapioca::app($appId);

					// Create app's admin
					$adminId = $user->get('id');

					static::$app->add_to_app( $adminId, 100 );
					static::$app->add_admin( $adminId );

					static::$user->add_to_app( $appId );

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
				$fields = array(
					'name'  => 'Les Bouffes du Nord',
					'slug'  => 'gtapiv2test',
				);

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