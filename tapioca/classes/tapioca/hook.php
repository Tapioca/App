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

use FuelException;
use Config;

class HookException extends FuelException {}

class Hook
{
    private static $namespace;
    private static $slug;
    private static $events;
    private static $hooks = null;

    public static function register(App $app, $collection)
    {
        if( !empty( $collection['hooks'] ) )
        {
            static::$hooks     = $collection['hooks'];
            static::$slug      = $app->get('slug');
            static::$namespace = ucfirst( static::$slug );

            \Module::load(static::$slug);
        }
    }

    public static function trigger($event, &$data, $status = null)
    {
        if( isset( static::$hooks[$event] ) )
        {
            foreach( static::$hooks[$event] as $cb)
            {

                if( filter_var($cb, FILTER_VALIDATE_URL) )
                {
                    static::curl_post_async($cb, $data, $status);
                }
                else
                {
                    $data = call_user_func_array('\\'.static::$namespace .'\\'.$cb, array($data, $status));
                }
            }
        }
    }

    public static function reset()
    {
        static::$hooks = null;
    }

    private static function curl_post_async($url, $data, $status)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_POST,true);
        curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $output = curl_exec($c);
        // if($output === false)
        // {
        //     trigger_error('Erreur curl : '.curl_error($c),E_USER_WARNING);
        // }

        curl_close($c);
    }
}