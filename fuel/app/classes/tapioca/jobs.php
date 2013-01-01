<?php

/**
 * Tapioca Jobs are based on Chris Boulton's Resque worker
 * and Lu Wang's MongoQueue
 *
 * https://github.com/chrisboulton/php-resque
 * https://github.com/lunaru/MongoQueue
 */

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
     * @var object Instance of the class performing work for this job.
     */
    private $instance;

    /**
     * @var object Object containing details of the job.
     */
    public $payload;

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
     * Instantiate a new instance of a job.
     *
     * @param object $payload Object containing details of the job.
     */
    public function __construct( $payload )
    {
        $this->payload = $payload;
    }

    /**
     * Generate a string representation used to describe the current job.
     *
     * @return string The string representation of the job.
     */
    public function __toString()
    {
        $name = array(
            'Job:' . (string) $this->payload['_id']
        );

        $name[] = $this->payload['job'];
        if(!empty($this->payload['args'])) {
            $name[] = json_encode($this->payload['args']);
        }
        return '(' . implode(' | ', $name) . ')';
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

    public static function push( $appslug, $jobName, $args, $priority = null )
    {
        $newJob = array(
            'appslug'   => $appslug,
            'job'       => $jobName, 
            'args'      => $args, 
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
            return new Jobs( $ret['value'] );
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
     * Update the status of the current job.
     *
     * @param int $status Status constant from Jobs indicating the current status of a job.
     */
    public function updateStatus($status)
    {
        if(empty($this->payload['_id'])) {
            return;
        }

        static::update( $this->payload['_id'], array('status' => $status) );
    }

    /**
     * Get the arguments supplied to this job.
     *
     * @return array Array of arguments.
     */
    public function getArguments()
    {
        if (!isset($this->payload['args'])) {
            return array();
        }
        
        return $this->payload['args'];
    }

    /**
     * Get the instantiated object for this job that will be performing work.
     *
     * @return object Instance of the object that this job belongs to.
     */
    public function getInstance()
    {
        if (!is_null($this->instance)) {
            return $this->instance;
        }

        if(!class_exists($this->payload['job'])) {
            throw new \JobsException(
                'Could not find job class ' . $this->payload['job'] . '.'
            );
        }

        if(!method_exists($this->payload['job'], 'perform')) {
            throw new \JobsException(
                'Job class ' . $this->payload['job'] . ' does not contain a perform method.'
            );
        }

        $this->instance = new $this->payload['job'];
        $this->instance->job = $this;
        $this->instance->args = $this->getArguments();
        return $this->instance;
    }

    /**
     * Actually execute a job by calling the perform method on the class
     * associated with the job with the supplied arguments.
     *
     * @throws Resque_Exception When the job's class could not be found or it does not contain a perform method.
     */
    public function perform()
    {
        try
        {
            $instance = $this->getInstance();            
        }
        catch( JobsException $e)
        {
            throw new \JobsException( $e->getMessage() );
        }


        try 
        {
            if(method_exists($instance, 'setUp')) {
                $instance->setUp();
            }

            $instance->perform();

            if(method_exists($instance, 'tearDown')) {
                $instance->tearDown();
            }
        }
        // beforePerform/setUp have said don't perform this job. Return.
        catch(Jobs_DontPerform $e) 
        {
            return false;
        }
        
        return true;
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