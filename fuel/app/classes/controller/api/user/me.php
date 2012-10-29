<?php

class Controller_Api_User_Me extends Controller_Api
{
	public function before()
	{
		parent::before();
	}

	public function get_index()
	{

		static::$data   = Tapioca::user()->get();
		static::$status = 200;
	}

	public function put_index()
	{
		$fields = Input::json(null, false);

		// user can not self promote
        if( isset( $fields['admin'] ) && $fields['admin'] )
        {
        	unset( $fields['admin'] );
        }    

		try
		{
			$user   = Tapioca::user();
			$action = $user->update( $fields );

			if( $action )
			{
				static::$data   = $user->get();
				static::$status = 200;					
			}
			else
			{
				static::error( __('tapioca.internal_server_error'), 500 );
			}
		}
		catch (UserException $e)
		{
			// catch errors such as bad fields
			static::error( $e->getMessage() );
		}
	}
}