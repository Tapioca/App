<?php

namespace Tapioca;

use FuelException;

class JobsException extends FuelException {}

class Jobs
{

    const STATUS_WAITING  = 1;
    const STATUS_RUNNING  = 2;
    const STATUS_FAILED   = 3;
    const STATUS_COMPLETE = 4;

    /**
     * @var  string  Database instance
     */
    protected static $db = null;

    /**
     * @var  string  MongoDb collection's name
     */
    protected static $dbCollectionName = null;

    /**
     * @var  int Archived job TTL
     */
    protected static $cleanQueue = null;


    /**
     * Load config
     */
    public static function _init()
    {
        static::$db               = \Mongo_Db::instance();
        static::$dbCollectionName = strtolower( \Config::get('tapioca.collections.queue') );
        static::$cleanQueue       = \Config::get('tapioca.cleanQueue');
    }

    /**
     * Push a new job in the queue.
     *
     * @param   string  App's slug
     * @param   string  Job to do
     * @param   array   Parameters to apply to the job
     * @param   int     Priority of the job
     * @return  string  MongoDb $id as token
     */

    public static function push( $appslug, $jobName, $parameters, $priority = null )
    {
        $newJob = array(
            'appslug'   => $appslug,
            'job'       => $jobName, 
            'params'    => $parameters, 
            'priority'  => $priority,
            'locked'    => null,
            'status'    => static::STATUS_WAITING,
            'pushed'    => new \MongoDate()
        );

        $result = static::$db->insert(static::$dbCollectionName, $newJob);

        return (string) $result;
    }

    /**
     * List all the jobs from the DB
     *
     * @param   string  restrict the results to this App
     * @return  object  MongoDb Hash
     */

    public static function get( $appslug = null )
    {
        $where = ( !is_null( $appslug )) ? array('appslug' => $appslug) : array();

        return static::$db
                ->where( $where )
                ->order_by(array(
                    'pushed' => 1
                ))
                ->hash( static::$dbCollectionName );
    }

    /**
     * Get list of job to do, sort by priority
     *
     * @return  object  MongoDb Hash
     */

    public static function has()
    {
        $query  = array(
            'status' => static::STATUS_WAITING,
            'locked' => null
        );

        $sort   = array(
            'priority' => -1
        );

        $update = array(
            'locked' => new \MongoDate(),
            'status' => static::STATUS_RUNNING
        );

        $ret = static::$db->command(
                    array('findandmodify' => static::$dbCollectionName,
                          'query'         => $query,
                          'sort'          => $sort,
                          'update'        => array('$set' => $update)
                    )
                );

        if( $ret['ok'] == 1 && !is_null( $ret['value'] ) )
        {
            return $ret['value'];
        }

        return false;
    }

    /**
     * Update job 
     *
     * @param   object  MongoId
     * @param   array   Fields to update
     * @return  bool
     */

    public static function update( $token, $update )
    {
        return static::$db
                    ->where( array( '_id' => $token ) )
                    ->update( static::$dbCollectionName, array('$set' => $update), array(), true );
    }

    /**
     * Delete all done jobs older than config limit 
     *
     * @return  bool
     */

    public static function clean()
    {
        $limitDate = new \MongoDate( time() - static::$cleanQueue );

        return static::$db
                        ->where(array(
                                'locked' => array('$lt' => $limitDate ),
                                'status' => array('$gt' => static::STATUS_RUNNING )
                            ))
                        ->delete_all( static::$dbCollectionName );
    }
}