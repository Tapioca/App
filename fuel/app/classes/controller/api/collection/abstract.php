<?php

class Controller_Api_Collection_Abstract extends Controller_Api
{
    protected static $appslug;
    private static $namespace;
    private static $collection;
    private static $ref;
    private static $locale;
    private static $revision;

    public function before()
    {
        parent::before();

        static::$appslug   = $this->param('appslug', false);
        static::$namespace = $this->param('namespace', false);
        static::$ref       = $this->param('ref', null);

        if( !static::$appslug )
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
            static::$collection = Tapioca::collection( static::$app, static::$namespace );
        }
        catch( TapiocaException $e )
        {
            static::error( $e->getMessage() );
            return;
        }

        static::$locale     = Input::get('l', null);
        static::$revision   = Input::get('r', null);

        Permissions::set( static::$user, static::$app );
    }

    public function get_index()
    {
        try
        {
            $collection = Tapioca::collection( static::$app, static::$namespace );
        }
        catch( TapiocaException $e )
        {
            static::error($e->getMessage());
            return;
        }

        try
        {
            $documents      = Tapioca::document( static::$app, $collection );
            static::$data   = $documents->abstracts( null, static::$ref );
            static::$status = 200;
        }
        catch ( DocumentException $e)
        {
            static::error($e->getMessage());
        }
    }

    //update document status.
    public function put_index()
    {
        $docStatus  = Input::json('status', null);

        if( !static::$ref || is_null( $docStatus ))
        {
            static::error( __('tapioca.missing_required_params') );
            return;
        }

        $document   = Tapioca::document(static::$app, static::$collection, static::$ref, static::$locale);
        $permission = $document->status_premission( static::$user->get('id') );

        try
        {
            Permissions::isGranted( $permission );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }
        
        static::$data   = $document->update_status( $docStatus, static::$revision ) ;
        static::$status = 200;
    }

    public function delete_index()
    {
        $document   = Tapioca::document(static::$app, static::$collection, static::$ref, static::$locale);
        $permission = $document->delete_premission( static::$user->get('id') );

        try
        {
            Permissions::isGranted( $permission );
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
        
        static::$data   = array('status' => $document->delete() );
        static::$status = 200;
    }
}