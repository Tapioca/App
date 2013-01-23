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

class Controller_Api_Document_Defined extends Controller_Api
{
    protected static $appslug;
    private static $collection;
    private static $document;
    private static $ref;

    public function before()
    {
        parent::before();

        static::$appslug    = $this->param('appslug', false);
        static::$ref        = $this->param('ref', null);

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
            $locale     = Input::get('l', null);

            static::$document = Tapioca::document(static::$app, static::$collection, static::$ref, $locale );
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
            Permissions::isGranted( 'app_read_documents' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        try
        {
            $revision = Input::get('r', null);

            // cast revision ID as integer
            if( !is_null( $revision ) )
            {
                $revision = (int) $revision;
            }
            
            $query = Input::get('q', null);

            // decode query
            if( !is_null( $query ) )
            {
                $query = json_decode($query, true);

                static::$document->set( $query );
            }

            static::$data   = static::$document->get( $revision );
            static::$status = 200;
        }
        catch ( DocumentException $e )
        {
            static::error( $e->getMessage() );
        }
    }

    public function put_index()
    {
        try
        {
            Permissions::isGranted( 'app_edit_documents' );
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
            }
            catch( DocumentException $e)
            {
                static::error( $e->getMessage() , 400 );
                return;
            }
        }
    }

    public function delete_index()
    {
        // todo: get document status to set appropriate permission
        // $permission = 'app_read_collections_' . static::$collection->summary['status'];

        try
        {
            Permissions::isGranted( 'app_delete_documents' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        if( ! static::deleteToken( 'document', static::$ref ))
        {
            return;
        }
        
        static::$data   = array('status' => static::$document->delete());
        static::$status = 200;
    }
}