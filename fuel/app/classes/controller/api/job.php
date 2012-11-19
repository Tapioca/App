<?php

class Controller_Api_Job extends Controller_Api
{
    public function before()
    {
        parent::before();

        try
        {
            Permissions::set( static::$user);
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
            Permissions::isGranted( 'list_jobs' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        static::$data   = Jobs::get();
        static::$status = 200;
    }
}