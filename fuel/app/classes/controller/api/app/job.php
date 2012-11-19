<?php

class Controller_Api_App_Job extends Controller_Api
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
            Permissions::isGranted( 'app_list_jobs' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        static::$data   = Jobs::get( static::$appslug );
        static::$status = 200;
    }
}