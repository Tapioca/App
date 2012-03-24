<?php

namespace Model;

use Auth;

class AttemptsException extends \Fuel_Exception {}
class UserSuspendedException extends \Model\AttemptsException {}

class Attempts extends \Model
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  string  Suspension collection name
	 */
	protected static $collection = null;

	/**
	 * @var  array  Stores suspension/limit config data
	 */
	protected static $limit = array();

	/**
	 * @var  string  Login id
	 */
	protected $login_id = null;

	/**
	 * @var  string  IP address
	 */
	protected $ip_address = null;

	/**
	 * @var  int  Number of login attempts
	 */
	protected $attempts = null;

	/**
	 * Attempts Constructor
	 *
	 * @param   string  user login
	 * @param   string  ip address
	 * @return  Montry_Attempts
	 * @throws  MontryAttemptsException
	 */
	public function __construct($login_id = null, $ip_address = null)
	{
		\Config::load('auth', true);

		static::$db = \Mongo_Db::instance();
		static::$collection = \Config::get('auth.collection.users_suspended');
		static::$limit = \Config::get('auth.limit');

		$this->login_id = $login_id;
		$this->ip_address = $ip_address;

		// limit checks
		if (static::$limit['enabled'] === true)
		{
			if ( ! is_int(static::$limit['attempts']) or static::$limit['attempts'] <= 0)
			{
				throw new \Model\AuthConfigException('invalid_limit_attempts');
			}

			if ( ! is_int(static::$limit['time']) or static::$limit['time'] <= 0)
			{
				throw new \Model\AuthConfigException('invalid_limit_time');
			}
		}

		if ($this->login_id)
		{
			$query = array('login_id' => $this->login_id);
		}

		if ($this->ip_address)
		{
			$query = array('ip' => $this->ip_address);
		}

		$result = static::$db->get_where(static::$collection, $query);

		foreach ($result as &$row)
		{
			// check if last attempt was more than 15 min ago - if so reset counter
			if ($row['last_attempt_at'] and ($row['last_attempt_at'] + static::$limit['time'] * 60) <= time())
			{
				$this->clear($row['login_id'], $row['ip']);
				$row['attempts'] = 0;
			}

			// check unsuspended time and clear if time is > than it
			if ($row['unsuspend_at'] and $row['unsuspend_at'] <= time())
			{
				$this->clear($row['login_id'], $row['ip']);
				$row['attempts'] = 0;
			}
		}

		if (count($result) > 1)
		{
			$this->attempts = $result;
		}
		elseif ($result)
		{
			$this->attempts = $result[0]['attempts'];
		}
		else
		{
			$this->attempts = 0;
		}

	}

	/**
	 * Check Number of Login Attempts
	 *
	 * @return  int
	 */
	public function get()
	{
		return $this->attempts;
	}

	/**
	 * Gets attempt limit number
	 *
	 * @return  int
	 */
	 public function get_limit()
	 {
	 	return static::$limit['attempts'];
	 }

	/**
	 * Add Login Attempt
	 *
	 * @param string
	 * @param int
	 */
	public function add()
	{
		// make sure a login id and ip address are set
		if (empty($this->login_id) or empty($this->ip_address))
		{
			throw new \Model\AttemptsException('login_ip_required');
		}

		// this shouldn't happen, but put it just to make sure
		if (is_array($this->attempts))
		{
			throw new \Model\AttemptsException('single_user_required');
		}

		if ($this->attempts)
		{
			$update = array(
							'attempts' => ++$this->attempts,
							'last_attempt_at' => time(),
						);

			$update_group = static::$db
								->where(array(
									'login_id' => $this->login_id,
									'ip' => $this->ip_address
								))
								->update(static::$collection, $update);
		}
		else
		{
			$result = static::$db->insert(static::$collection, array(
						'login_id' => $this->login_id,
						'ip' => $this->ip_address,
						'attempts' => ++$this->attempts,
						'last_attempt_at' => time(),
						'unsuspend_at' => null
					));
		}
	}

	/**
	 * Clear Login Attempts
	 *
	 * @param string
	 * @param string
	 */
	public function clear()
	{
		if ($this->login_id)
		{
			$query = array('login_id' => $this->login_id);
		}

		if ($this->ip_address)
		{
			$query = array('ip' => $this->ip_address);
		}

		$result = static::$db
						->where($query)
						->delete(static::$collection);

		$this->attempts = 0;
	}

	/**
	 * Suspend
	 *
	 * @param string
	 * @param int
	 */
	public function suspend()
	{
		if (empty($this->login_id) or empty($this->ip_address))
		{
			throw new \Model\UserSuspendedException('login_ip_required');
		}

		// only updates collection if unsuspended at has no value
		$query = array(
					'login_id' => $this->login_id,
					'ip'  => $this->ip_address,
					'$or' => array(array('unsuspend_at' => null), array('unsuspend_at' => 0)) 
				);

		$update = array(
						'suspended_at' => time(),
						'unsuspend_at' => time()+(static::$limit['time'] * 60),
					);

		$result = static::$db->where($query)->update(static::$collection, $update);

		throw new \Model\UserSuspendedException('user_suspended');
	}
}
