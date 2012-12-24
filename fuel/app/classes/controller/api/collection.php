<?php

class Controller_Api_Collection extends Controller_Api
{
    protected static $appslug;

    public function before()
    {
        parent::before();

        static::$appslug   = $this->param('appslug', false);

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
            Permissions::set( static::$user, static::$app );
        }
        catch( PermissionsException $e )
        {
            static::error($e->getMessage());
            return;
        }
    }

    public function get_index()
    {
        $availableStatus  = Config::get('tapioca.collection.status');
        $userCapabilities = array();

        foreach( $availableStatus as $status )
        {
            $permission = 'app_read_collections_' . $status;

            try
            {
                Permissions::isGranted( $permission );

                $userCapabilities[] = $status;
            }
            catch( PermissionsException $e){}
        }

        try
        {
            static::$data = Collection::getAll( static::$appslug, $userCapabilities );
            static::$status = 200;
        }
        catch (CollectionException $e)
        {
            static::error($e->getMessage());
        }
    }

    //create collection data.
    public function post_index()
    {
        try
        {
            Permissions::isGranted( 'app_create_collections' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        try
        {
            $collection = Tapioca::collection( static::$app ); 
        }
        catch (CollectionException $e)
        {
            static::error( $e->getMessage() );
        }

        $model      = Input::json();        
        $summary    = array();
        $schema     = array();
        $values     = $this->dispatch( $summary, $schema, $model );

        try
        {
            $summary = $collection->create_summary( $summary );

            if(count($schema) > 0)
            {
                $schema = $collection->update_data( $schema, static::$user );
            }

            static::$data   = $collection->get( null );
            static::$status = 200;

        }
        catch (CollectionException $e)
        {
            static::error($e->getMessage());
        }
    }
}