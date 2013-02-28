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

class Controller_Api_Library_Preset extends Controller_Api
{
    protected static $appslug;
    private static $file;

    public function before()
    {
        parent::before();

        static::$appslug    = $this->param('appslug', false);

        // check app exists
        if( static::$appslug && !static::assignApp() )
        {
            return;
        }

        // filename
        $filename  = $this->param('filename', null);
        $extension = Input::extension();

        if( is_null( $filename ) )
        {
            static::error( __('tapioca.missing_required_params'));
            return;
        }

        try
        {
            static::$file = Tapioca::library( static::$app, $filename );
        }
        catch( \TapiocaException $e)
        {
            static::error( $e->getMessage() );
        }
    }

    public function post_index()
    {
        // TODO: define capability
        if( static::$granted )
        {
            $preset = $this->param('preset', null);

            try
            {
                $ret = static::$file->preset( $preset );

                if( $ret )
                {
                    static::$data   = static::$file->get();
                    static::$status = 200;              
                }
                else
                {
                    static::error( __('tapioca.internal_server_error') );
                }
            }
            catch( \TapiocaException $e)
            {
                static::error( $e->getMessage() );
            }
        } 
    }
}