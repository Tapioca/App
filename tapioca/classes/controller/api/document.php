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

class Controller_Api_Document extends Controller_Api
{
    protected static $appslug;
    private static $collection;
    private static $document;

    public function before()
    {
        parent::before();

        static::$appslug    = $this->param('appslug', false);

        $namespace  = $this->param('namespace', false);

        // check collection's namespace 
        // and app exists
        if( static::$appslug && !static::assignApp() )
        {
            return;
        }

        // if no collection define
        if( !$namespace )
        {
            static::restricted();
            return;
        }

        // set permission
        try
        {
            Permissions::set( static::$user, static::$app );
        }
        catch( PermissionsException $e )
        {
            static::error($e->getMessage());
            return;
        }

        try
        {
            static::$collection = Tapioca::collection( static::$app, $namespace );
        }
        catch( TapiocaException $e )
        {
            static::error($e->getMessage());
            return;
        }

        // Document instance
        try
        {
            $locale = Input::get('l', null);

            static::$document = Tapioca::document(static::$app, static::$collection, null, $locale );
        }
        catch ( TapiocaException $e )
        {
            static::error( $e->getMessage() );
        }
    }

    /* Data
    ----------------------------------------- */

    public function get_index()
    {
        try
        {
            Permissions::isGranted( 'app_list_documents' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        try
        {
            $query = Input::get('q', null);

            // decode query
            if( !is_null( $query ) )
            {
                $query = json_decode($query, true);

                static::$document->set( $query );
            }

            static::$data = static::$document->getAll();
            static::$status = 200;
        }
        catch ( TapiocaException $e )
        {
            static::error( $e->getMessage() );
        }
    }

    //create collection data.
    public function post_index()
    {
        try
        {
            Permissions::isGranted( 'app_create_documents' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        $model = $this->clean();

        if( $model )
        {
            try
            {
                static::$data   = static::$document->save( $model, static::$user );
                static::$status = 200;

            } catch (DocumentException $e)
            {
                static::error( $e->getMessage(), 400 );
            }
        } // if model
    }
}