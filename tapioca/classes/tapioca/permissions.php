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

namespace Tapioca;

use Config;
use FuelException;

class PermissionsException extends FuelException {}

class Permissions
{
    private static $roles;
    private static $userRole = false;
    private static $permissions;

    public static function _init()
    {
        static::$roles       = Config::get('tapioca.roles');
        static::$permissions = Config::get('tapioca.default_premissions');
    }

    public static function set($user, $app = null)
    {
        if( !$user )
            return;

        if( $user instanceof User )
        {
            $isAdmin = $user->get('admin');
            $userId  = $user->get('id');

            if( !is_null( $app ) && !$isAdmin )
            {
                if( !$app->in_app( $userId ) )
                {
                    throw new \PermissionsException(
                        __('tapioca.user_not_in_app', array('app' => $app->get('name')))
                    );
                }

                foreach( $app->get('team') as $team )
                {
                    if( $team['id'] == $userId )
                    {
                        static::$userRole = $team['role'];
                        return; 
                    }
                }
            }

            if( $isAdmin )
            {
                static::$userRole = $isAdmin;
                return;
            }

            static::$userRole = 'guest';
        }
        else
        {
            try
            {
                $api = Tapioca::app( array( 'api.key' => $user ) );

                if( !is_null( $app ) && ( $api->get('name') != $app->get('name') ) )
                {
                    throw new \PermissionsException(
                        __('tapioca.user_not_in_app', array('app' => $app->get('name')))
                    );
                }

                static::$userRole = $api->get('api.role');
            }
            catch ( AuthException $e )
            {
                throw new \PermissionsException( 
                        __('tapioca.app_key_invalid', array( 'api' => $user) )
                    );
            }
        }
    }

    public static function isGranted( $capability )
    {
        $rolesPremissions = static::$permissions[ static::$userRole ];

        if( $rolesPremissions == '*' )
        {
            return true;
        }

        if( in_array($capability, $rolesPremissions) )
        {
            return true;
        }
        else
        {
            throw new \PermissionsException(
                __('tapioca.permissions_denied', array('capability' => $capability) )
            );
        }
    }
}