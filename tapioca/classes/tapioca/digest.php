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
use Config;

class DigestException extends FuelException {}

class Digest
{
    /**
     * @var  string  MongoDb collection's name
     */
    protected $dbCollection = null;

    /**
     * @var  object  App instance
     */
    protected $app = null;
    
    /**
     * @var  object  Collection instance
     */
    protected $collection = null;

    /**
     * @var  object  Current Document abstract
     */
    protected $abstract = null;

    /**
     * @var  string  Document ref
     */
    protected $ref = null;

    /**
     * @var  string  Document locale
     */
    protected $locale = null;

    /**
     * @var  int  active Document revision
     */
    protected $revisionActive = null;

    /**
     * @var  int  last revison 
     */
    protected $revisionLast = null;

    /**
     * Loads in the Document object
     *
     * @param   object  App instance
     * @param   object  Collection instance
     * @param   string  Document ref
     * @param   string  Document locale
     * @return  void
     */
    public function __construct(App $app, Collection $collection, $locale = null, $ref = null )
    {
        // load and set config
        $this->app          = $app;
        $this->dbCollection = $this->app->get('slug').'--digests';

        if( $collection instanceof Collection )
        {
            $this->collection   = $collection;
        }

        // Set Locale
        if( !is_null( $locale )  
            && in_array( $locale, $this->app->get('locales_keys') ) )
        {
            $this->locale = $locale;
        }
        else
        {
            $this->locale = $this->app->get('locale_default');
        }

        // if a Ref was passed
        if( $ref )
        {
            $this->ref = $ref; 

            // query database for document's abstract
            $abstract = Tapioca::db()
                        ->select(array(), array('_id'))
                        ->where(array(
                            '_ref'      => $ref,
                            'slug'      => $this->app->get('slug'),
                            'namespace' => $this->collection->namespace
                        ))
                        ->get_one( $this->dbCollection );

            // if there was a result
            if( $abstract )
            {
                $this->abstract = $abstract;
                
                // cache data
                $this->revisionActive = $this->abstract['revisions']['active'];
                $this->revisionLast   = $this->abstract['revisions']['total'];

                // define if document exists in selected locale
                if( !isset($this->abstract['revisions']['active'][ $this->locale ]) )
                {
                    $this->abstract['revisions']['active'][ $this->locale ] = null;
                }

                $this->revisionActive = $this->abstract['revisions']['active'][ $this->locale ];
            }
            // document doesn't exist
            else
            {
                throw new \DigestException(
                    __('tapioca.document_not_found', array('ref' => $this->ref, 'collection' => $this->collection->namespace))
                );
            }
        }
    }

    /**
     * Magic get method to allow getting class properties but still having them protected
     * to disallow writing.
     *
     * @return  mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Gets the documents abstracts of the collection
     *
     * @return  array
     * @throws  DocumentException
     */
    public function get( $status = null, $ref = null )
    {
        $where = array();

        // is that used somewhere ???
        if( !is_null( $status ) )
        {
            $where['revisions.list.status'] = (int) $status;
        }

        if( !is_null( $ref ) )
        {
            $where['_ref'] = $ref;
        }

        //query database for collections's abstracts
        $query =  Tapioca::db()
                    ->select( array(), array('_abstract') )
                    ->where( $where )
                    ->order_by( array(
                        'created' => -1
                    ) );

        if( !is_null( $ref ) )
        {
            $ret = $query->get_one( static::$dbCollection );

            if( $ret )
            {
                unset( $ret['_id'] );
                return $ret;
            }
            else
            {
                throw new \DigestException(
                    __('tapioca.document_not_found', array('ref' => $ref, 'collection' => static::$collection->namespace))
                );
            }
        }
        else
        {
            return $query->hash( static::$dbCollection, true);
        }
    }

    public function create( $ref, $digest, $userId )
    {
        $this->ref = $ref;
        $date      = new \MongoDate();
        $abstract  = array(
            '_ref'      => $ref,
            'slug'      => $this->app->get('slug'),
            'namespace' => $this->collection->namespace,
            'created'   => $date,
            'updated'   => $date,
            'revisions' => array(
                'total'   => (int) 1,
                'active'  => array( $this->locale => (int) 1 ),
                'list'    => array(
                                array(
                                    'revision' => (int) 1,
                                    'date'     => $date,
                                    'status'   => (int) 1,
                                    'locale'   => $this->locale,
                                    'user'     => $userId,
                                )
                            )
            )
        ) + $digest;

        $new_abstract = Tapioca::db()->insert( $this->dbCollection, $abstract );

        if( $new_abstract )
        {
            $this->abstract        = $abstract;
            $this->ref             = $ref;
            $this->revisionActive  = 1;
            $this->revisionLast    = 1;

            return $abstract;
        }

        return false;
    }


    public function update( $abstract )
    {
        if( is_null( $this->ref ) )
        {
            throw new \DigestException(
                __( 'tapioca.no_document_selected' )
            );
        }

        $new_abstract = Tapioca::db()
                            ->where(array(
                                '_ref'      => $this->ref,
                                'slug'      => $this->app->get('slug'),
                                'namespace' => $this->collection->namespace
                            ))
                            ->update( $this->dbCollection, $abstract );
    }

    public function delete()
    {
        if( is_null( $this->ref ) )
        {
            throw new \AbstractException( __('tapioca.no_document_selected') );
        }

        $delete =  Tapioca::db()
                        ->where(array(
                            '_ref'      => $this->ref,
                            'slug'      => $this->app->get('slug'),
                            'namespace' => $this->collection->namespace
                        ))
                        ->delete_all( $this->dbCollection );

        return;
    }

    public function newRevision()
    {
        ++$this->abstract['revisions']['total'];
        ++$this->revisionLast;

        return $this->revisionLast;
    }

    public function hasLocale()
    {
        // check if locale exists for this document
        if( !isset( $this->abstract['revisions']['active'][ $this->locale ] ) )
        {
            // try default locale first
            $this->locale = $this->app->get('locale_default');

            // if default locale doesn't exists, use first locale found
            if( !isset( $this->abstract['revisions']['active'][ $this->locale ] ) )
            {
                reset( $this->abstract['revisions']['active'] );
                $this->locale = key( $this->abstract['revisions']['active'] );
            }
        }

        return $this->locale;
    }

    /**
     * Check if new revision has higher status than the others
     * If we found a status 100 (published), return false
     *
     * @return  bool
     */
    public function isActive()
    {
        $higher = 1;
        
        foreach ($this->abstract['revisions']['list'] as $revision)
        {
            if($revision['locale'] == $this->locale)
            {
                $higher = ($revision['status'] > $higher) ? $revision['status'] : $higher;

                if($revision['status'] == 100)
                {
                    return false;
                }
            }
        }

        if($higher > 1)
        {
            return false;
        }

        return true;
    }
}