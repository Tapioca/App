<?php

class Controller_Api_App_Defined extends Controller_Api
{
    protected static $appslug;

    public function before()
    {
        parent::before();

        // to define working app
        static::$appslug = $this->param('appslug', false);

        if( !static::$appslug )
        {
            static::error( __('tapioca.missing_required_params') );
            return;
        }

        // app instance
        if( !static::assignApp() )
        {
            return;
        }

        try
        {
            Permissions::set( static::$user, static::$app );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }
    }

    public function get_index()
    {
        try
        {
            Permissions::isGranted( 'read_apps' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        static::$data   = static::$app->get();
        static::$status = 200;
    }

    public function put_index()
    {
        try
        {
            $ask = ( static::$app->in_app( static::$user->get('id') ) ) ? 'app_edit_settings' : 'edit_apps';
            
            Permissions::isGranted( $ask );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        try
        {
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

    public function delete_index()
    {
        try
        {
            Permissions::isGranted( 'delete_apps' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

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