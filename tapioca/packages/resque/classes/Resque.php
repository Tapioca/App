<?php

/**
 * Base Resque class.
 *
 * @package		Resque
 * @author		Chris Boulton <chris.boulton@interspire.com>
 * @copyright	(c) 2010 Chris Boulton
 * @license		http://www.opensource.org/licenses/mit-license.php
 */

namespace Resque;

class Resque
{
	const VERSION = '1.0';

	/**
	 * @var Resque_Redis Instance of Resque_Redis that talks to redis.
	 */
	public static $redis = null;

	/**
	* Loads in the config and sets the variables
	*/
	public static function _init()
	{
		\Config::load('resque', true);
		\Lang::load('resque', 'resque');

	}

	/**
	 * Given a host/port combination separated by a colon, set it as
	 * the redis server that Resque will talk to.
	 *
	 * @param mixed $server Host/port combination separated by a colon, or
	 * a nested array of servers with host/port pairs.
	 */
	public static function setBackend($server, $database = 0)
	{
		if(is_array($server)) {
			self::$redis = new RedisCluster($server);
		}
		else {
			list($host, $port) = explode(':', $server);
			self::$redis = new \Redis();
			self::$redis->pconnect($host, $port);
		}

        self::redis()->select($database);
	}

	/**
	 * Return an instance of the Resque_Redis class instantiated for Resque.
	 *
	 * @return Resque_Redis Instance of Resque_Redis.
	 */
	public static function redis()
	{
		if(is_null(self::$redis)) {
			self::setBackend('localhost:6379');
		}

		return self::$redis;
	}

	/**
	 * Push a job to the end of a specific queue. If the queue does not
	 * exist, then create it as well.
	 *
	 * @param string $queue The name of the queue to add the job to.
	 * @param object $item Job description as an object to be JSON encoded.
	 */
	public static function push($queue, $item)
	{
		self::redis()->sAdd('queues', $queue);
		self::redis()->rPush('queue:' . $queue, json_encode($item));
	}

	/**
	 * Pop an item off the end of the specified queue, decode it and
	 * return it.
	 *
	 * @param string $queue The name of the queue to fetch an item from.
	 * @return object Decoded item from the queue.
	 */
	public static function pop($queue)
	{
		$item = self::redis()->lPop('queue:' . $queue);
		if(!$item) {
			return;
		}

		return json_decode($item, true);
	}

	/**
	 * Return the size (number of pending jobs) of the specified queue.
	 *
	 * @return int The size of the queue.
	 */
	public static function size($queue)
	{
		return self::redis()->lSize('queue:' . $queue);
	}

	/**
	 * Create a new job and save it to the specified queue.
	 *
	 * @param string $queue The name of the queue to place the job in.
	 * @param string $class The name of the class that contains the code to execute the job.
	 * @param array $args Any optional arguments that should be passed when the job is executed.
	 * @param boolean $monitor Set to true to be able to monitor the status of a job.
	 */
	public static function enqueue($queue, $class, $args = null, $trackStatus = false)
	{
		$result = Resque_Job::create($queue, $class, $args, $trackStatus);
		if ($result) {
			Resque_Event::trigger('afterEnqueue', array(
				'class' => $class,
				'args' => $args,
			));
		}
		
		return $result;
	}

	/**
	 * Reserve and return the next available job in the specified queue.
	 *
	 * @param string $queue Queue to fetch next available job from.
	 * @return Resque_Job Instance of Resque_Job to be processed, false if none or error.
	 */
	public static function reserve($queue)
	{
		return Resque_Job::reserve($queue);
	}

	/**
	 * Get an array of all known queues.
	 *
	 * @return array Array of queues.
	 */
	public static function queues()
	{
		$queues = self::redis()->sMembers('queues');
		if(!is_array($queues)) {
			$queues = array();
		}
		return $queues;
	}
}
