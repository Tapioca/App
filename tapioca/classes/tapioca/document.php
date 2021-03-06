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

class DocumentException extends FuelException {}

class Document
{
    /**
     * @var  string  MongoDb collection's name
     */
    protected static $dbCollection = null;

    /**
     * @var  object  App instance
     */
    protected static $app = null;

    /**
     * @var  object  Collection instance
     */
    protected static $collection = null;

    /**
     * @var  string  Collection namespace
     */
    protected static $namespace = null;

    /**
     * @var  string  Document ref
     */
    protected static $ref = null;

    /**
     * @var  string  Document locale
     */
    protected static $locale = null;

    /**
     * @var  int  active Document revision
     */
    protected static $revisionActive = null;

    /**
     * @var  int  last revison 
     */
    protected static $revisionLast = null;

    /**
     * @var  array  selected Document
     */
    protected $document = null;

    /**
     * @var  array  selected Document's abstract
     */
    protected $abstract = null;

    /**
     * @var  array  document validation errors
     */
    protected $errors = array();

    /**
     * @var  array  Events list for hooks
     */
    protected $events = array();

    /**
     * @var  string  Query arguments
     */
    protected static $operators = array('select', 'where', 'sort', 'limit', 'skip');
    protected $select = array();
    protected $where  = array('_tapioca.status' => array('$ne' => -1));
    protected $sort   = array('_tapioca.created' => 'DESC');    
    protected $limit  = 99999;
    protected $skip   = 0;

    /**
     * Loads in the Document object
     *
     * @param   object  App instance
     * @param   object  Collection instance
     * @param   string  Document ref
     * @param   string  Document locale
     * @return  void
     */
    public function __construct(App $app, Collection $collection, $ref = null, $locale = null )
    {
        // load and set config
        static::$app          = $app;
        static::$collection   = $collection;
        static::$dbCollection = static::$app->get('slug').'-'.static::$collection->namespace;

        // Set Locale
        if( !is_null( $locale )  
            && in_array( $locale, static::$app->get('locales_keys') ) )
        {
            static::$locale = $locale;
        }
        else
        {
            static::$locale = static::$app->get('locale_default');
        }

        // if a Ref was passed
        if( $ref )
        {
            static::$ref = $ref; 

            // query database for document's abstract
            $abstract = Tapioca::db()
                        ->select(array(), array('_id'))
                        ->get_where( static::$dbCollection, array(
                            '_ref'      => $ref,
                            '_abstract' => array( '$exists' => true )
                        ), 1);

            // if there was a result
            if( count( $abstract ) == 1 )
            {
                $this->abstract = $abstract[0];
                
                // cache data
                static::$revisionActive = $this->abstract['revisions']['active'];
                static::$revisionLast   = $this->abstract['revisions']['total'];

                // define if document exists in selected locale
                if( !isset($this->abstract['revisions']['active'][static::$locale]) )
                {
                    $this->abstract['revisions']['active'][static::$locale] = null;
                }

                static::$revisionActive = $this->abstract['revisions']['active'][static::$locale];

                $this->set('where', array(
                        '_ref'            => static::$ref,
                        '_abstract'       => array( '$exists' => false ),
                        '_tapioca.locale' => static::$locale
                    ));
            }
            // document doesn't exist
            else
            {
                throw new \DocumentException(
                    __('tapioca.document_not_found', array('ref' => static::$ref, 'collection' => static::$collection->namespace))
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
     * Clean Document properties
     *
     * @return  void
     */
    public function reset()
    {
        static::$dbCollection = null;
        static::$app              = null;
        static::$ref              = null;
        static::$locale           = null;
        static::$revisionActive   = null;
        static::$revisionLast     = null;
        $this->document           = null;
        $this->abstract           = null;
        $this->errors             = array();

        Hook::reset();
    }

    /**
     * Gets the documents abstracts of the collection
     *
     * @return  array
     * @throws  DocumentException
     */
    public function abstracts( $status = null, $ref = null )
    {
        $where = array(
                    '_abstract' => array( '$exists' => true )
                );

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
                    ->select( array(), array('_abstract'))
                    ->where($where)
                    ->order_by(array(
                        'created' => -1
                    ));

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
                throw new \DocumentException(
                    __('tapioca.document_not_found', array('ref' => $ref, 'collection' => static::$collection->namespace))
                );
            }
        }
        else
        {
            return $query->hash( static::$dbCollection, true);
        }
    }

    /**
     * Query Definition
     * usage : $db->set('where', array('foo' => 'bar'));
     * usage : $db->set(array('where' => array('foo' => 'bar'), 'select' => array('foo', 'bar'));
     *
     * @param   string|array   
     * @param   string  
     */ 
    public function set($operator, $value = '')
    {   
        if(is_string($operator))
        {
            static::_set($operator, $value);
        }
        
        if(is_array($operator))
        {
            foreach($operator as $key => $value)
            {
                static::_set($key, $value);
            }
        }
    }
    
    private function _set($operator, $value)
    {       
        if(in_array($operator, static::$operators))
        {
            if(is_array($value))
            {
                $tmp_arr = $this->$operator;

                foreach($value as $key => $val)
                {
                    $tmp_arr[$key] = $val;
                }

                $this->$operator = $tmp_arr;
            }
            else // string or int
            {
                $this->$operator = $value;
            }
        }
    }

    private function _unset($operator, $key)
    {
        $tmp_arr = $this->$operator;

        if(isset($tmp_arr[$key]))
        {
            unset($tmp_arr[$key]);

            $this->$operator = $tmp_arr;
        }
    }

    /**
     *  Query database for individual document
     *
     * @param   int Revision number 
     * @return  array 
     * @throws  DocumentException
     */
    public function get( $revision = null )
    {
        if( is_null(static::$collection) )
        {
            throw new \DocumentException( __('tapioca.no_collection_selected') );
        }

        // check if locale exists for this document
        if( !isset( $this->abstract['revisions']['active'][static::$locale] ) )
        {
            // try default locale first
            static::$locale = static::$app->get('locale_default');

            // if default locale doesn't exists, use first locale found
            if( !isset( $this->abstract['revisions']['active'][static::$locale] ) )
            {
                reset( $this->abstract['revisions']['active'] );
                static::$locale = key($this->abstract['revisions']['active']);
            }
        }

        $this->set('where', array(
            '_ref'            => static::$ref,
            '_abstract'       => array( '$exists' => false ),
            '_tapioca.active' => true,
            '_tapioca.locale' => static::$locale
        ));

        // get a specific revison
        if( !is_null($revision) )
        {
            $this->_unset('where', '_tapioca.active');
            $this->_unset('where', '_tapioca.status');
            $this->_unset('where', '_tapioca.locale');

            $this->set('where', array('_tapioca.revision' => (int) $revision));
        }
        else if( !is_null(static::$revisionActive) )
        {
            $this->set('where', array('_tapioca.revision' => static::$revisionActive));
        }

        $result = Tapioca::db()
            ->select( $this->select, array('_id', '_tapioca') )// always exclude _id && _tapioca properties
            ->where( $this->where )
            ->order_by( $this->sort )
            ->get( static::$dbCollection );

        if( $result )
        {
            if( isset( $result[0]['_id'] ) )
                unset( $result[0]['_id'] );
            
            // return individual document
            return $result[0];
        }
        
        if( static::$ref )
        {
            if( !is_null( $revision ) )
            {
                throw new \DocumentException(
                    __('tapioca.document_revision_not_found', array('ref' => static::$ref, 'collection' => static::$collection->namespace, 'revision' => $revision))
                );
            }
            else
            {
                throw new \DocumentException(
                    __('tapioca.document_not_found', array('ref' => static::$ref, 'collection' => static::$collection->namespace))
                );
            }
        }

        // return no result
        return array();
    }

    public function getAll()
    {
        if( is_null(static::$collection) )
        {
            throw new \DocumentException(__('tapioca.no_collection_selected'));
        }

        // always return _ref property
        if( count($this->select) > 0)
        {
            $this->set('select', array_merge($this->select, array('_ref')) );
        }

        // if query contains more than sort _tapioca.created, remove it
        // then add it back as last option for sorting.
        if( count($this->sort) > 1)
        {
            $this->_unset('sort', '_tapioca.created');
            $this->set('sort', array('_tapioca.created' => 'DESC'));
        }

        $this->set('where', array(
                    '_abstract'       => array( '$exists' => false ),
                    '_tapioca.status' => 100,
                    '_tapioca.active' => true,
                    '_tapioca.locale' => static::$locale
                ));

        $result = Tapioca::db()
            ->select( $this->select, array('_tapioca') ) // always exclude _tapioca property
            ->where( $this->where )
            ->order_by( $this->sort )
            ->limit( $this->limit )
            ->offset( $this->skip )
            ->hash( static::$dbCollection, true );

        return $result;
    }

    /**
     * API facade for create or update a document
     *
     * @param   array document data
     * @return  object User instance
     * @throws  DocumentException
     */
    public function save(array $document, User $user)
    {
        if( is_null(static::$collection) )
        {
            throw new \DocumentException(__('tapioca.no_collection_selected'));
        }

        $collectionData = static::$collection->data();

        // Test document rules
        if( count( $collectionData['rules'] ) > 0)
        {
            if( !Tapioca::checkRules( $collectionData['rules'], $document ) )
            {
                throw new \DocumentException( __('tapioca.document_failed_at_rules_validation') );
            }
        }

        // Cast document's values
        Cast::set($collectionData['cast'], $document);

        Hook::register(static::$app, $collectionData);

        // Global before hooks
        Hook::trigger('document::before', $document);

        // Get document digest
        try
        {
            $digest = $this->set_digest($collectionData['digest']['fields'], $document);
        }
        catch (DocumentException $e)
        {
            throw new \DocumentException( $e->getMessage() );
        }

        // new document
        if( is_null( static::$ref ) )
        {
            Hook::trigger('document::before::new', $document);

            $ret = $this->create($document, $digest, $user);

            if($ret)
            {
                static::$collection->inc_document();
            }
            
            Hook::trigger('document::after::new', $document);
        }
        else // update Document
        {
            Hook::trigger('document::before::update', $document);

            $this->update($document, $digest, $user);

            Hook::trigger('document::after::update', $document);
        }

        Hook::trigger('document::after', $document);

        Search::index( static::$app->get('slug'), static::$collection->namespace, static::$ref, $digest['digest'] );

        return $this->get( static::$revisionLast );
    }

    private function create($document, $digest, $user)
    {
        $date = new \MongoDate();

        $ref = uniqid();

        $data = array(
            '_ref'      => $ref,
            '_tapioca' => array(
                'revision' => (int) 1,
                'status'   => (int) 1,
                'active'   => (bool) true,
                'created'  => $date,
                'updated'  => $date,
                'locale'   => static::$locale
            )
        ) + $document;

        $abstract = array(
            '_ref'      => $ref,
            '_abstract' => (bool) true, 
            'created'   => $date,
            'updated'   => $date,
            'revisions' => array(
                'total'   => (int) 1,
                'active'  => array(static::$locale => (int) 1),
                'list'    => array(
                                array(
                                    'revision' => (int) 1,
                                    'date'     => $date,
                                    'status'   => (int) 1,
                                    'locale'   => static::$locale,
                                    'user'     => $user->get('id'),
                                )
                            )
            )
        ) + $digest;

        $new_data = Tapioca::db()->insert(static::$dbCollection, $data);

        if($new_data)
        {
            $new_abstract = Tapioca::db()->insert(static::$dbCollection, $abstract);

            if($new_abstract)
            {
                $this->abstract          = $abstract;
                static::$ref             = $ref;
                static::$revisionActive  = 1;

                return true;
            }
        }
    }

    private function update($document, $digest, $user)
    {
        ++static::$revisionLast;
        
        $is_active = $this->is_active();

        $date = new \MongoDate();

        $data = array(
            '_ref'   => static::$ref,
            '_tapioca' => array(
                'revision' => (int) static::$revisionLast,
                'status'   => (int) 1,
                'active'   => (bool) $is_active,
                'created'  => $this->abstract['created'],
                'updated'  => $date,
                'locale'   => static::$locale
            )
        ) + $document;

        ++$this->abstract['revisions']['total'];

        // update disgest only if revison is active
        if( $is_active )
        {
            $this->abstract['digest'] = $digest['digest'];          
        }

        $this->abstract['updated']             = $date;
        $this->abstract['revisions']['list'][] = array(
                                                    'revision' => (int) static::$revisionLast,
                                                    'date'     => $date,
                                                    'status'   => (int) 1,
                                                    'locale'   => static::$locale,
                                                    'user'     => $user->get('id'),
                                                );

        // update active revision to false
        if( $is_active )
        {
            $update_active = Tapioca::db()
                                ->where(array(
                                    '_ref'            => static::$ref,
                                    '_tapioca.locale' => static::$locale
                                ))
                                ->update_all(static::$dbCollection, array('_tapioca.active' => (bool) false));

            $this->abstract['revisions']['active'][static::$locale] = (int) static::$revisionLast;
        }

        // insert new revision
        $new_data = Tapioca::db()->insert(static::$dbCollection, $data);

        // update document abstract
        if( $new_data )
        {
            $new_abstract = Tapioca::db()
                                ->where(array(
                                    '_ref'      => static::$ref,
                                    '_abstract' => (bool) true
                                ))
                                ->update(static::$dbCollection, $this->abstract);
        }
    }

    public function delete()
    {
        if( is_null( static::$collection ) )
        {
            throw new \DocumentException( __('tapioca.no_collection_selected') );
        }

        if( is_null( static::$ref ) )
        {
            throw new \DocumentException(__('tapioca.no_document_selected'));
        }

        $delete =  Tapioca::db()
                        ->where(array(
                                '_ref' => static::$ref
                        ))
                        ->delete_all(static::$dbCollection);

        if($delete)
        {
            Search::delete( static::$app->get('slug'), static::$collection->namespace, static::$ref );

            // Get Collection Definiton
            try
            {
                static::$collection->inc_document( -1 );
            }
            catch (TapiocaException $e)
            {
                throw new \DocumentException( $e->getMessage() );
            }

            return true;
        }
    }

    /**
     * Update document status
     * if no revison ID provided, update active revision
     * if revision Id different from active revision, update digest
     *
     * @param  int    status (-1 > 100)
     * @param  int    revision Id
     * @return array  Document abstract
     * @throws DocumentException
     */

    public function update_status($status, $revision = null)
    {
        if( is_null( static::$collection ) )
        {
            throw new \DocumentException(__('tapioca.no_collection_selected'));
        }

        if( is_null( static::$ref ) )
        {
            throw new \DocumentException(__('tapioca.no_document_selected'));
        }

        if( is_null( $revision ) )
        {
            $revision = static::$revisionActive;
        }

        $revision = (int) $revision;

        $set_out_of_date = ($status == 100);
        $document        = $this->get( $revision );
        $collectionData  = static::$collection->data();

        Hook::register(static::$app, $collectionData );

        // Before status update hooks
        Hook::trigger('status::before', $document, $status);

        foreach ($this->abstract['revisions']['list'] as &$value)
        {
            if($value['revision'] == $revision)
            {
                $value['status'] = (int) $status;
            }
            else if($set_out_of_date) // set other revision "Out of date"
            {
                if($value['locale'] == static::$locale)
                {
                    $value['status'] = -1;
                }
            }
        }

        // Update abstract digest
        if($revision != static::$revisionActive && $set_out_of_date)
        {
            try
            {
                $digest = $this->set_digest($collectionData['digest']['fields'], $document);
            }
            catch (DocumentException $e)
            {
                throw new \DocumentException( $e->getMessage() );
            }

            $this->abstract['digest'] = $digest['digest'];
        }

        // new revison's status
        $update = array('_tapioca.status' => (int) $status);

        // if new status is 100 (Published),
        // we set the others revisons at -1 (out of date)
        // and define the revision as "Active"
        if($set_out_of_date)
        {
            $this->abstract['revisions']['active'][static::$locale] = $revision;

            $update_no_active = Tapioca::db()
                                    ->where(array(
                                        '_ref'              => static::$ref,
                                        '_tapioca.revision' => array('$ne' => $revision),
                                        '_tapioca.locale'   => static::$locale,
                                        '_abstract'         => array( '$exists' => false )
                                    ))
                                    ->update_all(static::$dbCollection, array(
                                        '_tapioca.active' => (bool) false,
                                        '_tapioca.status' => (int) -1
                                    ));

            $update['_tapioca.active'] = (bool) true;
        }

        // Update revision status
        $update_doc = Tapioca::db()
                            ->where(array(
                                '_ref'              => static::$ref,
                                '_tapioca.revision' => $revision
                            ))
                            ->update(static::$dbCollection, $update);

        // prevent mongodb crash
        if(isset($this->abstract['_id']))
        {
            unset($this->abstract['_id']);
        }

        // Update Documant abstract 
        $new_abstract = Tapioca::db()
                            ->where(array(
                                '_ref'      => static::$ref,
                                '_abstract' => (bool) true
                            ))
                            ->update(static::$dbCollection, $this->abstract);

        // if new status is 100,
        // update dependencies
        if($set_out_of_date)
        {
            $resqueArgs = array(
                'appslug'    => static::$app->get('slug'),
                'collection' => static::$collection->namespace,
                '_ref'       => static::$ref,
                'locale'     => static::$locale,
                'revision'   => $revision
            );
            
            // check for documents to update
            Tapioca::enqueueJob( static::$app->get('slug'), '\\Tapioca\\Jobs\\Dependency\\Document', $resqueArgs, null);
        }

        // After status update hooks
        Hook::trigger('status::after', $document, $status);

        return $this->abstract;
    }

    /**
     * Define witch capability is required to edit status document
     *
     * @param    string  User Id
     */
    public function status_premission( $userId )
    {
        $owner = $this->abstract['revisions']['list'][0]['user'];

        return ( $userId != $owner ) ? 'app_publish_others_documents' : 'app_publish_documents';
    }

    /**
     * Define witch capability is required to delete the document
     *
     * @param    string  User Id
     */
    public function delete_premission( $userId )
    {
        $highest = -1;

        foreach ($this->abstract['revisions']['list'] as &$value)
        {
            if($value['status'] > $highest)
            {
                $highest = $value['status'];
            }
        }

        $status = ( $highest == 100 ) ? 'published_' : '';
        $owner  = ( $this->abstract['revisions']['list'][0]['user'] != $userId) ? 'others_' : '';

        return 'app_delete_'. $owner . $status .'documents';
    }

    /**
     * Empty the Collection from all this document  
     * /!\ WARNING: no backup!!
     *
     * @return  void
     */
    public function drop()
    {
        if( is_null( static::$collection ) )
        {
            throw new \DocumentException(__('tapioca.no_collection_selected'));
        }

        $database = Config::get('db.mongo.default.database');
        $delete   = Tapioca::db()->drop_collection($database, static::$dbCollection);

        if($delete)
        {
            static::$collection->reset_document();

            return true;
        }
    }

    /**
     * Extract document digest 
     *
     * @param   array collection digest definition
     * @param   array document content
     * @return  array document digest
     * @throws  DocumentException
     */
    private function set_digest($structure, $document)
    {
        $locale_default = static::$app->get('locale_default');

        if(static::$locale != $locale_default && isset($this->abstract['revisions']['active'][$locale_default]))
        {
            return array('digest' => $this->abstract['digest']);
        }

        $summary = array('digest' => array());
        
        $preventDuplicateKey = 0;

        foreach($structure as $v)
        {
            $value = \Arr::get($document, $v['path'], '__DOC_MISSING_VALUE__');

            if($value != '__DOC_MISSING_VALUE__')
            {
                // prevent MongoDb crash if key look like:
                // deep.nested.key
                $arrK = explode('.', $v['path']);
                $storedKey = (count($arrK) > 1) ? end($arrK) : $v['path'];

                if( array_key_exists( $storedKey, $summary['digest']) )
                {
                    $storedKey = $storedKey. ++$preventDuplicateKey;
                }

                $summary['digest'][$storedKey] = $value;
            }
            else
            {
                throw new \DocumentException(
                    __('tapioca.document_column_is_empty', array('column' => $v))
                );
            }
        }

        return $summary;
    }

    // ??? to remove ?
    private function set_locale_revision($revision = null)
    {
        $localeRevision = array();
        $revisionActive = (!is_null($revision)) ? $revision : static::$revisionActive;
        $totalRevision  = (count($this->abstract['revisions']['list']) - 1);
        $revisions      = $this->abstract['revisions']['list'];

        for($r = $totalRevision; $r >= 0; --$r)
        {
            if($revisions[$r]['locale'] == static::$locale)
            {
                if($revisions[$r]['revision'] == $revisionActive)
                {
                    $revisions[$r]['active'] = true;
                }
                $revisions[$r]['date'] = $revisions[$r]['date']->sec;

                $localeRevision[]      = $revisions[$r];
            }
        }

        return $localeRevision;
    }

    /**
     * Check if new revision has higher status than the others
     * If we found a status 100 (published), return false
     *
     * @return  bool
     */
    private function is_active()
    {
        $higher = 1;
        
        foreach ($this->abstract['revisions']['list'] as $revision)
        {
            if($revision['locale'] == static::$locale)
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

    /**
    *   --------------------------------------------------------------------------------
    *   Debug: LastQuery
    *   --------------------------------------------------------------------------------
    *
    *   Return Data about last query
    */
    
    public function last_query()
    {
        return array(
            'select'    => $this->select,
            'where'     => $this->where,
            'limit'     => $this->limit,
            'skip'      => $this->skip,
            'sort'      => $this->sort
        );
    }
    
}