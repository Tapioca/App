<?php

namespace Resque;

class RedisCluster
{
	/**
	* Collection of Redis objects attached to Redis servers
	* @var array
	* @access private
	*/
	private $redis;
	
	/**
	 * Aliases of Redis objects attached to Redis servers, used to route commands to specific servers
	 * @see RedisCluster::to
	 * @var array
	 * @access private
	 */
	private $aliases;
	
	/**
	 * Hash ring of Redis server nodes
	 * @var array
	 * @access private
	 */
	private $ring;
	
	/**
	 * Individual nodes of pointers to Redis servers on the hash ring
	 * @var array
	 * @access private
	 */
	private $nodes;
	
	/**
	 * Number of replicas of each node to make around the hash ring
	 * @var integer
	 * @access private
	 */
	private $replicas = 128;
	
	/**
	 * The commands that are not subject to hashing
	 * @var array
	 * @access private
	 */
	private $dont_hash = array(
			'RANDOMKEY', 'DBSIZE',
			'SELECT',    'MOVE',    'FLUSHDB',  'FLUSHALL',
			'SAVE',      'BGSAVE',  'LASTSAVE', 'SHUTDOWN',
			'INFO',      'MONITOR', 'SLAVEOF'
	);
	
	/**
	 * Creates a Redis interface to a cluster of Redis servers
	 * @param array $servers The Redis servers in the cluster. Each server should be in the format array('host' => hostname, 'port' => port)
	 */
	function __construct($servers) {
		$this->ring = array();
		$this->aliases = array();
		foreach ($servers as $alias => $server) {
			$redis = new Redis();
			$redis->pconnect($server['host'], $server['port']);
			$this->redis[] = $redis;
			if (is_string($alias)) {
				$this->aliases[$alias] = $this->redis[count($this->redis)-1];
			}
			for ($replica = 1; $replica <= $this->replicas; $replica++) {
				$this->ring[crc32($server['host'].':'.$server['port'].'-'.$replica)] = $this->redis[count($this->redis)-1];
			}
		}
		ksort($this->ring, SORT_NUMERIC);
		$this->nodes = array_keys($this->ring);
	}
	
	/**
	 * Routes a command to a specific Redis server aliased by {$alias}.
	 * @param string $alias The alias of the Redis server
	 * @return Redis The Redis object attached to the Redis server
	 */
	function to($alias) {
		if (isset($this->aliases[$alias])) {
			return $this->aliases[$alias];
		}
		else {
			throw new Exception("That Redis alias does not exist");
		}
	}
	
	/* Execute a Redis command on the cluster */
	function __call($name, $args) {
	
		/* Pick a server node to send the command to */
		$name = strtoupper($name);
		if (!in_array($name, $this->dont_hash)) {
			$node = $this->nextNode(crc32($args[0]));
			$redis = $this->ring[$node];
		}
		else {
			$redis = $this->redis[0];
		}
	
		/* Execute the command on the server */
		return call_user_func_array(array($redis, $name), $args);
	}
	
	/**
	 * Routes to the proper server node
	 * @param integer $needle The hash value of the Redis command
	 * @return Redis The Redis object associated with the hash
	 */
	private function nextNode($needle) {
		$haystack = $this->nodes;
		while (count($haystack) > 2) {
			$try = floor(count($haystack) / 2);
			if ($haystack[$try] == $needle) {
				return $needle;
			}
			if ($needle < $haystack[$try]) {
				$haystack = array_slice($haystack, 0, $try + 1);
			}
			if ($needle > $haystack[$try]) {
				$haystack = array_slice($haystack, $try + 1);
			}
		}
		return $haystack[count($haystack)-1];
	}
}