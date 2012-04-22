<?php

class PhpRedisException extends \FuelException {}


class Phpredis 
{
	protected static $instances = array();
	//protected $connection = false;
	protected $r = false;
	
	public static function instance($name = 'default')
	{
		if (\array_key_exists($name, static::$instances))
		{
			return static::$instances[$name];
		}

		if (empty(static::$instances))
		{
			\Config::load('db', true);
		}

		if ( ! ($config = \Config::get('db.redis.'.$name)))
		{
			throw new \PhpRedisException('Invalid instance name given.');
		}
		
		$db = new static($config);

		static::$instances[$name] = $db->r;

		return static::$instances[$name];
	}

	public function  __construct(array $config = array())
	{
		$this->r = new Redis();
		$connection = $this->r->connect($config['hostname'], $config['port']);
		
		if ( ! $connection)
		{
			throw new \PhpRedisException();
		}
		
		$this->r->setOption(Redis::OPT_PREFIX, $config['prefix']); // use custom prefix on all keys
		$this->r->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP); // use built-in serialize/unserialize

		return $this;
	}

}