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

class Controller_Api_Preview extends Controller_Api
{
    protected static $appslug;

    public function before()
    {
        parent::before();

        static::$appslug = $this->param('appslug', false);
        
        // check collection's namespace 
        // and app exists
        if( static::$appslug && !static::assignApp() )
        {
            return;
        }
    }

    public function get_index()
    {
        try
        {
            $previewId = $this->param('id', false);

            static::$data   = Preview::get( $previewId );
            static::$status = 200;
        }
        catch ( PreviewException $e )
        {
            static::error( $e->getMessage(), 400 );
        }
    }

    public function post_index()
    {
        $model = $this->clean();

        $namespace = $this->param('id', false);

        try
        {
            $collection = Tapioca::collection( static::$app, $namespace );
        }
        catch( TapiocaException $e )
        {
            static::error($e->getMessage());
            return;
        }

        if( $model )
        {
            try
            {
                static::$data   = Preview::save( $model, static::$app, $collection );
                static::$status = 200;

            } catch ( PreviewException $e)
            {
                static::error( $e->getMessage() );
            }
        } // if model
    }
}