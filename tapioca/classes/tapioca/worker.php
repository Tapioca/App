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

/**
 * Tapioca Workers are based on Chris Boulton's Resque worker
 *
 * https://github.com/chrisboulton/php-resque
 */

namespace Tapioca;

use Config;
use FuelException;

class WorkerException extends FuelException {}

class Worker
{
    const VERSION = '1.0';
    const LOG_NONE = 0;
    const LOG_NORMAL = 1;
    const LOG_VERBOSE = 2;

    /**
     * @var int Current log level of this worker.
     */
    public $logLevel = 0;

    /**
     * @var string The hostname of this worker.
     */
    private $hostname;

    /**
     * @var string String identifying this worker.
     */
    private $id;

    /**
     * @var boolean True if on the next iteration, the worker should shutdown.
     */
    private $shutdown = false;

    /**
     * @var boolean True if this worker is paused.
     */
    private $paused = false;

    /**
     * @var int Process ID of child worker processes.
     */
    private $child = null;

    /**
     * @var object mongoDb connection
     */
    private $db = null;

    public function __construct()
    {
        if(function_exists('gethostname'))
        {
            $hostname = gethostname();
        }
        else
        {
            $hostname = php_uname('n');
        }

        $this->hostname = $hostname;
        $this->id       = $this->hostname . ':'.getmypid();
        $this->db       = \Mongo_Db::instance();
    }

    /**
     * Generate a string representation of this worker.
     *
     * @return string String identifier for this worker instance.
     */
    public function __toString()
    {
        return $this->id;
    }

    /**
     * The primary loop for a worker which when called on an instance starts
     * the worker's life cycle.
     *
     * Queues are checked every $interval (seconds) for new jobs.
     *
     * @param int $interval How often to check for new jobs across the queues.
     */
    public function work($interval = 5)
    {
        $this->updateProcLine('Starting');
        $this->registerSigHandlers();

        while(true)
        {
            if( $this->shutdown )
            {
                break;
            }

            // Attempt to find and reserve a job
            $job = false;

            if( !$this->paused )
            {
                $job = $this->reserve();
            }

            if( !$job )
            {
                // If no job was found, we sleep for $interval before continuing and checking again
                $this->log('Sleeping for ' . $interval, true);
                
                if($this->paused)
                {
                    $this->updateProcLine('Paused');
                }

                usleep($interval * 1000000);
                continue;
            }

            $this->log( $job );

            $this->child = $this->fork();

            // Forked and we're the child. Run the job.
            if($this->child === 0 || $this->child === false)
            {
                $status = 'Processing ' . $job . ' since ' . strftime('%F %T');
                $this->updateProcLine($status);
                $this->log($status, self::LOG_VERBOSE);
                $this->perform($job);
                if($this->child === 0) {
                    exit(0);
                }
            }

            if($this->child > 0) 
            {
                // Parent process, sit and wait
                $status = 'Forked ' . $this->child . ' at ' . strftime('%F %T');
                $this->updateProcLine($status);
                $this->log($status, self::LOG_VERBOSE);

                // Wait until the child process finishes before continuing
                pcntl_wait($status);
                $exitStatus = pcntl_wexitstatus($status);
                
                if($exitStatus !== 0)
                {
                    $this->log('Job exited with exit code ' . $exitStatus, self::LOG_VERBOSE);
                    // $job->fail(new Resque_Job_DirtyExitException(
                    //     'Job exited with exit code ' . $exitStatus
                    // ));
                }
            }

            $this->child = null;

        }
    }

    /**
     * Process a single job.
     *
     * @param object|null $job The job to be processed.
     */
    public function perform( $job )
    {
        try 
        {
            $job->updateStatus( Jobs::STATUS_RUNNING );

            $job->perform();
        }
        catch(JobsException $e) 
        {
            $this->log($job . ' failed: ' . $e->getMessage());

            $update = array(
                'status' => Jobs::STATUS_FAILED,
                'log'    => $e->getMessage()
            );

            Jobs::update( $job->payload['_id'], $update );

            return;
        }

        $job->updateStatus( Jobs::STATUS_COMPLETE );

        $this->log('done ' . $job );
    }

    /**
     * Attempt to find a job.
     *
     * @return object|boolean Instance of Tapioca\Job if a job is found, false if not.
     */
    public function reserve()
    {

        $this->log('Checking for job', self::LOG_VERBOSE);
        $job = Jobs::has();

        if( $job )
        {
            $this->log('Found job', self::LOG_VERBOSE);

            return $job;
        }

        return false;
    }

    /**
     * Attempt to fork a child process from the parent to run a job in.
     *
     * Return values are those of pcntl_fork().
     *
     * @return int -1 if the fork failed, 0 for the forked child, the PID of the child for the parent.
     */
    private function fork()
    {
        if(!function_exists('pcntl_fork')) {
            return false;
        }

        $pid = pcntl_fork();
        if($pid === -1) {
            throw new RuntimeException('Unable to fork child worker.');
        }

        return $pid;
    }

    /**
     * On supported systems (with the PECL proctitle module installed), update
     * the name of the currently running process to indicate the current state
     * of a worker.
     *
     * @param string $status The updated process title.
     */
    private function updateProcLine($status)
    {
        if(function_exists('setproctitle')) {
            setproctitle('worker-' . self::VERSION . ': ' . $status);
        }
    }

    /**
     * Register signal handlers that a worker should respond to.
     *
     * TERM: Shutdown immediately and stop processing jobs.
     * INT: Shutdown immediately and stop processing jobs.
     * QUIT: Shutdown after the current job finishes processing.
     * USR1: Kill the forked child immediately and continue processing jobs.
     */
    private function registerSigHandlers()
    {
        if(!function_exists('pcntl_signal')) {
            $this->log('no pcntl_signal function');
            return;
        }

        declare(ticks = 1);
        pcntl_signal(SIGTERM, array($this, 'shutDownNow'));
        pcntl_signal(SIGINT,  array($this, 'shutDownNow'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGUSR1, array($this, 'killChild'));
        pcntl_signal(SIGUSR2, array($this, 'pauseProcessing'));
        pcntl_signal(SIGCONT, array($this, 'unPauseProcessing'));
        pcntl_signal(SIGPIPE, array($this, 'reestablishRedisConnection'));
        $this->log('Registered signals', self::LOG_VERBOSE);
    }

    /**
     * Signal handler callback for USR2, pauses processing of new jobs.
     */
    public function pauseProcessing()
    {
        $this->log('USR2 received; pausing job processing');
        $this->paused = true;
    }

    /**
     * Signal handler callback for CONT, resumes worker allowing it to pick
     * up new jobs.
     */
    public function unPauseProcessing()
    {
        $this->log('CONT received; resuming job processing');
        $this->paused = false;
    }

    /**
     * TODO
     * Signal handler for SIGPIPE, in the event the MongoDB connection has gone away.
     * Attempts to reconnect to MongoDB, or raises an Exception.
     */
    public function reestablishRedisConnection()
    {
        $this->log('SIGPIPE received; attempting to reconnect');
        $this->db   = \Mongo_Db::instance();
    }

    /**
     * Schedule a worker for shutdown. Will finish processing the current job
     * and when the timeout interval is reached, the worker will shut down.
     */
    public function shutdown()
    {
        $this->shutdown = true;
        $this->log('Exiting...');
    }

    /**
     * Force an immediate shutdown of the worker, killing any child jobs
     * currently running.
     */
    public function shutdownNow()
    {
        $this->shutdown();
        $this->killChild();
    }

    /**
     * Kill a forked child job immediately. The job it is processing will not
     * be completed.
     */
    public function killChild()
    {
        if(!$this->child) {
            $this->log('No child to kill.', self::LOG_VERBOSE);
            return;
        }

        $this->log('Killing child at ' . $this->child, self::LOG_VERBOSE);
        if(exec('ps -o pid,state -p ' . $this->child, $output, $returnCode) && $returnCode != 1) {
            $this->log('Killing child at ' . $this->child, self::LOG_VERBOSE);
            posix_kill($this->child, SIGKILL);
            $this->child = null;
        }
        else {
            $this->log('Child ' . $this->child . ' not found, restarting.', self::LOG_VERBOSE);
            $this->shutdown();
        }
    }

    /**
     * Output a given log message to STDOUT.
     *
     * @param string $message Message to output.
     */
    public function log($message)
    {
        if($this->logLevel == self::LOG_NORMAL) {
            fwrite(STDOUT, "*** " . $message . "\n");
        }
        else if($this->logLevel == self::LOG_VERBOSE) {
            fwrite(STDOUT, "** [" . strftime('%T %Y-%m-%d') . "] " . $message . "\n");
        }
    }

}