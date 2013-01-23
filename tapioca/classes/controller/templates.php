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

/**
 * Merge Handlebars views into a big hash.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Templates extends Controller
{
    public function action_index()
    {
        // Load Tapioca language file
        Tapioca::base();
        
        $path    = APPPATH.'views/tpl';
        $files   = File::read_dir($path);
        $arr     = static::parse( $files, $path );
        
        $headers = array ('Content-Type' => 'text/javascript');

        $body    = '$.Tapioca.Tpl = '.Format::forge( $arr )->to_json();

        return new Response( $body, 200, $headers );
    }

    private static function parse( $files, $path )
    {
        $arr = array();

        foreach($files as $key => $filename)
        {
            if( is_array( $filename ) )
            {
                $tmp = array_filter( explode('/', $key) );
                $key = end( $tmp );

                $arr[ $key ] = self::parse( $filename, $path.DS.$key );
            }
            else
            {
                $name = str_replace('.php', '', $filename);

                $html         = View::forge( $path.DS.$filename )->auto_filter( false )->render();
                $arr[ $name ] = trim( str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $html) );
            }
        }

        return $arr;        
    }
}
