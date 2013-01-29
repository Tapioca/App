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

        // udpate user account
        try
        {
            $user   = Tapioca::user();

            // update user password
            if( isset( $fields['oldpass'] ) &&  isset( $fields['newpass'] ) )
            {
                $action = $user->change_password( $fields['newpass'], $fields['oldpass'] );
            }

            // make sure we remove new/old password
            if( isset( $fields['oldpass'] ) &&  isset( $fields['newpass'] ) )
            {
                unset( $fields['oldpass'] );
                unset( $fields['newpass'] );
            }

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
            static::error( $e->getMessage(), 403 );
        }
    }
}