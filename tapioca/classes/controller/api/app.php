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

class Controller_Api_App extends Controller_Api
{
    public function before()
    {
        parent::before();

        try
        {
            Permissions::set( static::$user );
        }
        catch( PermissionsException $e )
        {
            static::error($e->getMessage());
            return;
        }
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
            // Backbone use 'PUT' if `slug` is set.
            if( isset( $fields['slug-sugest'] ) && !empty( $fields['slug-sugest'] ) )
            {
                $fields['slug'] = $fields['slug-sugest'];
                unset( $fields['slug-sugest'] );
            }

            $appId = Tapioca::app()->create($fields);
            
            if( $appId )
            {
                static::$app = Tapioca::app($appId);

                // Create app's admin
                static::$app->add_to_app( $userId, 'admin' );
                // static::$app->add_admin( $userId );

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