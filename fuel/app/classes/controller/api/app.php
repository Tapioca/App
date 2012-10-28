<?php

class Controller_Api_App extends Controller_Api
{
    public function before()
    {
        parent::before();

        Permissions::set( static::$user );
    }

    public function get_index()
    {
        try
        {
            Permissions::isGranted( 'list_apps' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        $set = Input::get('set', null);

        static::$data   = App::getAll( $set );
        static::$status = 200;
    }

    public function post_index()
    {
        try
        {
            Permissions::isGranted( 'create_apps' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

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