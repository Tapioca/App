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

namespace Tapioca;

use FuelException;

class PreviewException extends FuelException {}

class Preview
{
    /**
     * @var  string  Database instance
     */
    protected static $db = null;
    
    /**
     * @var  string  MongoDb collection's name
     */
    protected static $dbCollectionName = null;

    public static function _init()
    {
        static::$db               = \Mongo_Db::instance();
        static::$dbCollectionName = \Config::get('tapioca.collections.previews');
    }

    /**
     * get document's preview
     *
     * @param   string  preview ID
     * @return  array   Document
     * @throws  PreviewException
     */
    public static function get( $previewId )
    {
        $limitDate  = ( time() - \Config::get('tapioca.previewLimit') );
        $object     = static::$db->get_where( static::$dbCollectionName, array(
                            '_id' => new \MongoId( $previewId ),
                        ));

        if( count( $object ) != 1 )
        {
            throw new \PreviewException( __('tapioca.no_valid_token') );
        }

        if( !isset($object[0]['_tapioca_date']) || $object[0]['_tapioca_date'] <= $limitDate )
        {
            throw new \PreviewException( __('tapioca.token_expire') );
        }

        return static::clean( $object[0] );

    }

    /**
     * Create a new preview for a document
     *
     * @param   array document data
     * @param   object App instance
     * @param   object Collection instance
     * @return  array Document
     * @throws  PreviewException
     */
    public static function save(array $document, App $app = null, Collection $collection = null)
    {
        if( is_null( $collection ) )
        {
            throw new \PreviewException(__('tapioca.no_collection_selected'));
        }

        $collectionData = $collection->data();

        // Test document rules
        if( count( $collectionData['rules'] ) > 0)
        {
            if( !Tapioca::checkRules( $collectionData['rules'], $document ) )
            {
                throw new \PreviewException( __('tapioca.document_failed_at_rules_validation') );
            }
        }

        Hook::register( $app, $collectionData );

        // Cast document's values
        Cast::set($collectionData['cast'], $document);

        // Global before hooks
        Hook::trigger('document::before', $document);

        Hook::trigger('document::before::new', $document);

        $limitDate  = ( time() + \Config::get('tapioca.previewLimit') );

        $document = array(
            '_tapioca_date' => $limitDate,
        ) + $document;

        $ret = static::$db->insert( static::$dbCollectionName, $document );
        
        Hook::trigger('document::after::new', $document);

        Hook::trigger('document::after', $document);

        // clean return
        return static::clean( $document );
    }

    private static function clean( $document )
    {
        $document['_id'] = (string) $document['_id'];

        unset( $document['_tapioca_date']);

        return $document;
    }
}