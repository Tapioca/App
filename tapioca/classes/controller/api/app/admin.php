<?php
/**
 * Tapioca: Schema Driven Data Engine 
 * Flexible CMS build on top of MongoDB, FuelPHP and Backbone.js
 *
 * @package   Tapioca
 * @version   v0.8
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/App
 */

class Controller_Api_App_Admin extends Controller_Api
{
    protected static $appslug;
    protected static $userId;

    public function before()
    {
        parent::before();

        static::$appslug = $this->param('appslug', false);
        static::$userId  = $this->param('userid', false);

        if( !static::$appslug || !static::$userId )
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

    public function post_index()
    {
        try
        {
            Permissions::isGranted( 'app_promote_users' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

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

    public function delete_index()
    {
        try
        {
            Permissions::isGranted( 'app_promote_users' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

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