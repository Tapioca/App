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

class CollectionException extends FuelException {}

class Collection
{
    /**
     * @var  string  Database instance
     */
    protected static $db = null;

    /**
     * @var  object  Active app
     */
    protected static $app = null;

    /**
     * @var  array  Collection's name for exception message
     */
    protected $name = null;

    /**
     * @var  array  Collection's namespace for exception message
     */
    protected $namespace = null;

    /**
     * @var  array  Collection's summary
     */
    protected $summary = null;

    /**
     * @var  array  Collection's data
     */
    protected $data = array();

    /**
     * @var  string  MongoDb collection's name
     */
    protected static $dbCollectionName = null;

    /**
     * @var  array  Cache Summary where clause
     */
    protected static $summaryWhere = array();

    /**
     * @var  array  Events list for hooks
     */
    protected $hook = array();

    /**
     * @var  array  List of fields who need to be cast, get from config
     */
    protected static $castable = array();

    /**
     * @var  array  path and value of fields to cast
     */
    protected $castablePath = array();

    /**
     * @var  array  List of fields who need dependencies, get from config
     */
    protected static $dependencies = array();

    /**
     * @var  array  path to dependencies
     */
    protected $dependenciesPath = array();

    /**
     * @var  array  path and label of digest fields
     */
    protected $digestPath = array();

    /**
     * @var  array  does user alter natural digest order
     */
    protected $digestEdit = 0;

    /**
     * @var  array  path and label of fieds who need validation
     */
    protected $rulesPath = array();

    /**
     * Loads in the Collection object
     *
     * @param   object  App instance
     * @param   string  Collection namespace
     * @return  void
     * @throws  CollectionException
     */
    public function __construct(App $app, $namespace = false )
    {
        // load and set config
        static::$app        = $app;
        static::$dbCollectionName = strtolower(Config::get('tapioca.collections.collections'));
        static::$db         = \Mongo_Db::instance();

        // if an namespace was passed
        if( $namespace )
        {
            //query database for collection's summary
            $summary = static::$db->get_where(static::$dbCollectionName, array(
                'namespace' => $namespace,
                'type'      => 'summary',
                'app_id'    => static::$app->get('slug')
            ), 1);

            // if there was a result
            if (count($summary) == 1)
            {
                //query database for collection's summary
                $data = static::$db
                            ->where(array(
                                'app_id'    => static::$app->get('slug'),
                                'namespace' => $summary[0]['namespace'],
                                'type'      => 'data'
                            ))
                            ->order_by(array(
                                'revision'  => 'asc'
                            ))
                            ->get(static::$dbCollectionName);

                $this->summary   = $summary[0];
                $this->data      = $data;
                $this->namespace = $summary[0]['namespace'];
                $this->name      = $summary[0]['name'];

                if(isset($summary[0]['hook']))
                {
                    $this->hook = $summary[0]['hook'];
                }

                $this->set_summary_where();

            }
            // collection doesn't exist
            else
            {
                throw new \CollectionException(
                    __('tapioca.collection_not_found', array('collection' => $namespace))
                );
            }
        }
    }

    /**
     * Format "where" query for the current collection.
     *
     * @return  void
     */
    private function set_summary_where()
    {
        static::$summaryWhere = array( 'app_id'    => static::$app->get('slug'),
                                        'namespace' => $this->namespace,
                                        'type'      => 'summary');
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
     * Gets the summaries of all collections,
     *
     * @param   string    app slug
     * @param   array     collection status that user can read
     * @return  array
     */
    public static function getAll( $appslug, $status = array('public') )
    {
        static::$dbCollectionName = strtolower(Config::get('tapioca.collections.collections'));
        static::$db               = \Mongo_Db::instance();

        //query database for collections's summaries
        $ret = static::$db
                ->select(array(
                        'name',
                        'namespace',
                        'documents',
                        'status',
                        'digest'
                    ), array(
                    'revisions'
                ))
                ->where(array(
                    'app_id' => $appslug,
                    'type'   => 'summary'
                ))
                ->where_in('status', $status)
                ->order_by( array( 'name' => 'ASC' ) )
                ->hash( static::$dbCollectionName, true );

        // foreach( $ret->results as &$row)
        // {
        //     $row['url']    = \Router::get('api_collection_defined', array('appslug' => $appslug, 'namespace' => $row['namespace']));
        //     $row['digest'] = $row['digest']['fields'];
        // }

        return $ret;
    }

    /**
     * Gets the current collections definition
     *
     * @params  int Revision number
     * @return  array
     * @throws  CollectionException
     */

    public function get( $revision = null )
    {
        if( !is_null( $revision ) && is_numeric( $revision ))
        {
            $revision = (int) $revision;
        }

        $data       = $this->data( $revision );

        // Format return
        $ret            = array_merge($this->summary, $data);

        unset($ret['_id']);
        unset($ret['type']);

        return $ret;
    }

    /**
     * Gets the summary of the current collection
     *
     * @return  array
     * @throws  CollectionException
     */
    public function summary()
    {
        if(is_null($this->summary))
        {
            throw new \CollectionException( __('tapioca.no_collection_selected') );
        }
        return $this->summary;
    }

    /**
     * Gets the data of the current collection
     * if no revison ID set, return last revision
     *
     * @param   int Revision number
     * @return  array
     * @throws  CollectionException
     */
    public function data($revision = null)
    {
        if(is_null($this->summary))
        {
            throw new \CollectionException( __('tapioca.no_collection_selected') );
        }

        // get a specific revison
        if(!is_null($revision))
        {
            // revisons array is zero based index
            --$revision;

            // revision exists
            if(isset($this->data[$revision]))
            {
                return $this->data[$revision];
            }

            throw new \CollectionException(
                __('tapioca.collection_revision_not_found', array('collection' => $this->name, 'revision' => ++$revision))
            );
        }

        if( !count( $this->data ) )
            return array();

        return end($this->data);
    }

    /**
     * check for required fields
     *
     * @param   array  Fields to update
     * @param   string list to check
     * @return  bool
     * @throws  CollectionException
     */
    private static function validation(array $fields, $check_list)
    {
        foreach($check_list as $item)
        {
            if(!isset($fields[$item]) || empty($fields[$item]))
            {
                throw new \CollectionException(
                    __('tapioca.collection_column_is_empty', array('column' => $item))
                );
            }
        }
    }

    /**
     * Create collection's summary
     *
     * @param   array  Fields
     * @return  bool
     * @throws  CollectionException
     */
    public function create_summary(array $fields)
    {

        $namespace = (isset( $fields['namespace-suggest'] ) ) ? $fields['namespace-suggest'] : $fields['name'];

        $fields['namespace'] = \Inflector::friendly_title($namespace, '-', true);

        if($this->namespance_exists($fields['namespace']))
        {
            throw new \CollectionException(
                __('tapioca.collection_already_exists', array('name' => $fields['name']))
            );
        }

        // check for required fields
        $check_list = Config::get('tapioca.validation.collection.summary');

        self::validation($fields, $check_list);

        $status = 'draft';

        if( isset( $fields['status'] ) )
        {
            $status = $fields['status'];
            unset( $fields['status'] );
        }

        $new_summary = array(
            'app_id'    => static::$app->get('slug'),
            'type'      => 'summary',
            'documents' => (int) 0,
            'status'    => $status,
            'revisions' => array()
        ) + $fields;

        $this->summary   = $new_summary;
        $this->namespace = $new_summary['namespace'];
        $this->name      = $new_summary['name'];
        $this->data      = array();

        $this->set_summary_where();

        return static::$db->insert(static::$dbCollectionName, $new_summary);
    }

    /**
     * Update the current collection's summary
     *
     * @param   array  Fields to update
     * @return  bool
     * @throws  CollectionException
     */
    public function update_summary(array $fields)
    {
        if(is_null($this->summary))
        {
            throw new \CollectionException( __('tapioca.no_collection_selected') );
        }

        // check for required fields
        $check_list = Config::get('tapioca.validation.collection.summary');

        self::validation($fields, $check_list);

        if(isset($fields['status']))
        {
            $fields['status'] = $fields['status'];
        }

        $update =  static::$db
                        ->where(static::$summaryWhere)
                        ->update(static::$dbCollectionName, $fields);

        if($update)
        {
            $this->summary = array_merge($this->summary, $fields);

            return true;
        }

        return false;
    }

    /**
     * Add a new schema revision to the current collection
     *
     * @param   array  Fields
     * @param   object User object instance
     * @return  bool
     * @throws  CollectionException
     */
    public function update_data(array $fields, User $user)
    {
        if(is_null($this->summary))
        {
            throw new \CollectionException(__('tapioca.no_collection_selected'));
        }

        // check for required fields
        $check_list = Config::get('tapioca.validation.collection.data');

        self::validation($fields, $check_list);

        static::$castable     = Config::get('tapioca.cast');
        static::$dependencies = Config::get('tapioca.dependencies');

        $this->digestEdit = \Arr::get($fields, 'digest.edited', false);

        $this->parse($fields['schema']);

        $arrData    = Config::get('tapioca.collection.dispatch.data');

        $revision = (count($this->data) + 1);
        $defaults = array();

        $data = array(
            'app_id'      => static::$app->get('slug'),
            'type'        => 'data',
            'active'      => true,
            'namespace'   => $this->namespace,
            'revision'    => $revision,
            'digest'     => array(
                'fields' => ( $this->digestEdit ) ? $fields['digest']['fields'] : $this->digestPath,
                'edited' => $this->digestEdit
            ),
            'cast'         => $this->castablePath,
            'rules'        => $this->rulesPath,
            'schema'       => $fields['schema'],
            'dependencies' => $this->dependenciesPath, //( isset( $fields['dependencies'] )) ? $fields['dependencies'] : $defaults,
            'hooks'        => ( isset( $fields['hooks'] ))        ? $fields['hooks']        : '',
            'indexes'      => ( isset( $fields['indexes'] ))      ? $fields['indexes']      : $defaults,
            'template'     => ( isset( $fields['template'] ))     ? $fields['template']     : $defaults,
        );

        $revision = array(
            'revison' => $revision,
            'date'    => new \MongoDate(),
            'user'    => $user->get('id'),
            // 'status'  => (int) 100
        );

        // set previous revision as "non active"
        $update_no_active = static::$db
                                ->where(array(
                                    'app_id'    => static::$app->get('slug'),
                                    'namespace' => $this->namespace,
                                    'type'      => 'data'
                                ))
                                ->update_all(static::$dbCollectionName, array(
                                    'active' => (bool) false
                                ));

        $insert_data = static::$db->insert(static::$dbCollectionName, $data);

        if($insert_data)
        {
            //update previous revisions status
            // foreach($this->summary['revisions'] as &$r)
            // {
            //  $r['status'] = -1;
            // }

            $this->summary['revisions'][] = $revision;
            $this->data[] = $data;

            $update_summary = static::$db
                                ->where(static::$summaryWhere)
                                ->update(static::$dbCollectionName, array(
                                    'revisions' => $this->summary['revisions'],
                                    'digest'    => $data['digest']
                                ));

            if(!$update_summary)
            {
                throw new \CollectionException(
                    __('tapioca.can_not_update_collection_revision', array('name' => $this->name))
                );
            }

            return true;
        }

        throw new \CollectionException(
            __('tapioca.can_not_insert_collection_data', array('name' => $this->name))
        );
    }

    /**
     * Increment/decrement total documents in Collection
     *
     * @param   int   Increment|Decrement
     * @throws  CollectionException
     * @return  void
     */
    public function inc_document($direction = 1)
    {
        if(is_null($this->summary))
        {
            throw new \CollectionException(__('tapioca.no_collection_selected'));
        }

        $ret = static::$db->command(
                    array('findandmodify' => static::$dbCollectionName,
                          'query'         => static::$summaryWhere,
                          'update'        => array('$inc' => array('documents' => (int) $direction)),
                          'new'           => true
                    )
                );

        if($ret['ok'] == 1)
        {
            $this->summary['documents'] = $ret['value']['documents'];
        }
    }

    /**
     * Reset total documents in Collection
     *
     * @throws  CollectionException
     * @return  void
     */
    public function reset_document()
    {
        if(is_null($this->summary))
        {
            throw new \CollectionException(__('tapioca.no_collection_selected'));
        }

        $update = static::$db
                    ->where(static::$summaryWhere)
                    ->update(static::$dbCollectionName, array('$set' => array('documents' => (int) 0)), array(), true);

        if($update)
        {
            $this->summary['documents'] = (int) 0;
        }
    }

    /**
     * Delete the current collection
     *
     * @return  bool
     * @throws  CollectionException
     */
    public function delete()
    {
        if(is_null($this->summary))
        {
            throw new \CollectionException(__('tapioca.no_collection_selected'));
        }

        return static::$db
                    ->where(array(
                            'namespace' => $this->namespace,
                            'app_id'    => static::$app->get('slug')
                    ))
                    ->delete_all(static::$dbCollectionName);
    }

    /**
     * Check if namespace exists already
     *
     * @param   string  The namespace value
     * @return  bool
     */
    private function namespance_exists($namespace)
    {
        // query db to check for login_column
        $result = static::$db->get_where(static::$dbCollectionName, array(
                                                'app_id'    => static::$app->get('slug'),
                                                'namespace' => $namespace,
                                                'type'      => 'summary'
                                            ), 1);
        if (count($result) == 1)
        {
            return $result[0];
        }

        return false;
    }

    /**
     * Collection fields who need a post traitment
     *
     * @param   object
     * @param   string  use for recustion
     * @return  bool
     */
    private function parse(&$schema, $path = '/')
    {
        foreach($schema as &$item)
        {
            if($item['type'] == 'object' || $item['type'] == 'array')
            {
                $tmp_path = $path.$item['id'].'/';
                $this->parse($item['node'], $tmp_path);
            }
            else
            {
                $tmp_path = $path.$item['id'];

                // cast fields
                if(in_array($item['type'], static::$castable))
                {
                    $obj = new \stdClass;
                    $obj->path = $tmp_path;
                    $obj->type = $item['type'];

                    $this->castablePath[] = $obj;

                    if( !isset( $item['rules']) )
                    {
                        $item['rules'] = array();
                    }

                    if($item['type'] == 'number' && !isset( $item['rules']))
                    {
                        $item['rules'] = array('numeric');
                    }

                    if(!in_array('numeric', $item['rules']))
                    {
                        $item['rules'][] = 'numeric';
                    }
                }

                // dependencies
                if(in_array($item['type'], static::$dependencies))
                {
                    $obj       = new \stdClass;
                    $obj->path = substr(str_replace('/', '.', $tmp_path), 1);

                    if( $item['type'] != 'file' && isset( $item['embedded'] ) )
                    {
                        $obj->collection = $item['collection'];
                        $obj->fields     = $item['embedded'];
                    }

                    if( $item['type'] == 'file' )
                    {
                        $obj->collection = static::$app->get('slug').'--library';
                    }

                    if( isset( $obj->collection ) )
                    {
                        $this->dependenciesPath[] = $obj;
                    }
                }

                // summary
                if(!$this->digestEdit) // if we don't set summary mannualy
                {
                    if(isset($item['summary']) && $item['summary'])
                    {
                        $itemPath = static::setItemPath($path, $item['id']);

                        $obj = new \stdClass;
                        $obj->path  = $itemPath;
                        $obj->label = $item['label'];

                        $this->digestPath[] = $obj;

                        if(!isset( $item['rules']))
                        {
                            $item['rules'] = array('required');
                        }

                        if(!in_array('required', $item['rules']))
                        {
                            $item['rules'][] = 'required';
                        }
                    }
                }

                // rules
                if(isset($item['rules']) && count($item['rules']) > 0)
                {
                    $obj = new \stdClass;
                    $obj->path  = $tmp_path;
                    $obj->rules = $item['rules'];

                    $this->rulesPath[] = $obj;
                }
            }
        }
    }

    /**
     * Format field path
     *
     * @param   string  xpath
     * @param   string  ite ID
     * @return  string
     */
    private static function setItemPath($path, $id)
    {
        $itemPath = $path.$id;
        $itemPath = str_replace('/', '.', $itemPath);
        $itemPath = substr($itemPath, 1); // remove root

        return $itemPath;
    }
}