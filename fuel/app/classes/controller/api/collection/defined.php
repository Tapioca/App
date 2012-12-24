<?php

class Controller_Api_Collection_Defined extends Controller_Api
{
    protected static $appslug;
    private static $namespace;
    private static $collection;

    public function before()
    {
        parent::before();

        static::$appslug   = $this->param('appslug', false);
        static::$namespace = $this->param('namespace', false);

        if( !static::$appslug || !static::$namespace )
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
            Permissions::set( static::$user, static::$app );
        }
        catch( PermissionsException $e )
        {
            static::error($e->getMessage());
            return;
        }

        try
        {
            static::$collection = Tapioca::collection(static::$app, static::$namespace);
        }
        catch (CollectionException $e)
        {
            static::error( $e->getMessage() );
        }


    }

    public function get_index()
    {
        $permission = 'app_read_collections_' . static::$collection->summary['status'];

        try
        {
            Permissions::isGranted( $permission );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        $revision     = Input::get('r', null);

        static::$data   = static::$collection->get( $revision );
        static::$status = 200;
        
    }

    //update collection data.
    public function put_index()
    {
        try
        {
            Permissions::isGranted( 'app_edit_collections' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        $model   = Input::json();
        $summary = array();
        $schema  = array();
        
        $this->dispatch( $summary, $schema, $model );

        try
        {
            // format previous revision as new to compare.
            // Goals is to know if we have a new revision or just the same data
            // QUESTION: this migth be in the Collection Class ?
            $foo      = array();
            $previous = array();

            $this->dispatch( $foo, $previous, static::$collection->data() );
        }
        catch (CollectionException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        try
        {
            $summary = static::$collection->update_summary($summary);

            ksort($previous);
            ksort($schema);

            $previousString = json_encode($previous);
            $schemaString   = json_encode($schema);

            // TODO: find a better way to make a diff
            if( $previousString !== $schemaString )
            {
                $schema = static::$collection->update_data($schema, static::$user);
            }

            static::$data   = static::$collection->get( null );
            static::$status = 200;

        }
        catch (CollectionException $e)
        {
            static::error( $e->getMessage() );
        }
    }

    public function delete_index()
    {
        try
        {
            Permissions::isGranted( 'app_delete_collections' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        if( ! static::deleteToken( 'collection', static::$namespace ))
        {
            return;
        }

        $data = static::$collection->delete(); 

        static::$data   = array('status' => $data);
        static::$status = 200;
    }


    public function delete_drop()
    {
        try
        {
            Permissions::isGranted( 'app_empty_collections' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        $namespace = $this->param('namespace', false);
        $documents = Tapioca::document(static::$app, static::$collection);
        $delete    = $documents->drop();

        static::$data   = array('status' => $delete);
        
        static::$status = 200;
    }
}