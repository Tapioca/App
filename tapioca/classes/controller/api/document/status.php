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

class Controller_Api_Document_Status extends Controller_Api
{
    protected static $appslug;
    private static $collection;
    private static $ref;
    private static $revision;
    private static $locale;

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

        try
        {
            static::$collection = Tapioca::collection( static::$app, $namespace );
        }
        catch( TapiocaException $e )
        {
            static::error($e->getMessage());
            return;
        }

        static::$locale     = Input::get('l', null);
        static::$revision   = Input::get('r', null);

        // cast revision ID as integer
        if( !is_null( static::$revision ) )
        {
            static::$revision = (int) static::$revision;
        }
    }

    //update document status.
    public function put_index()
    {
        if( static::$granted && static::$ref)
        {
            $document  = Tapioca::document(static::$app, static::$collection, static::$ref, static::$locale);
            $docStatus = Input::json('status', null);

            if( is_null( $docStatus ) )
            {
                static::$data   = array('error' => __('tapioca.missing_required_params'));
                static::$status = 500;
            }
            else
            {
                static::$data   = array('revisions' => $document->update_status( $docStatus, static::$revision ) );
                static::$status = 200;
            }
        }
    }
}