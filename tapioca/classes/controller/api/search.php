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

class Controller_Api_Search extends Controller_Api
{
    protected static $appslug;
    private static $file;
    private static $filename;

    public function before()
    {
        parent::before();

        static::$appslug    = $this->param('appslug', false);

        // check app exists
        if( static::$appslug && !static::assignApp() )
        {
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
    }

    // get file listing
    public function get_index()
    {
        try
        {
            Permissions::isGranted( 'app_list_documents' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 401 );
            return;
        }
        
        static::$data   = Search::get( static::$appslug );
        static::$status = 200;
    }
}