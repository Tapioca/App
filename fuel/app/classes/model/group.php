<?php

/*

APPS SCHEMA


{
	"_id": MongoId(),
	"name": ,
	"slug": ,
	"account':
	{
		"registred":,
		"activited":,
		"type": ENUM(free, prenium)
	},
	"db": 
	{
		"name":,
		"user":,
		"pass":,
		"host":,
		"apikey":
	},
	"admins": 
	[
		user MongoId,
	]
	"team":
	[
		{
			"_id": ,
			"name": ,
			"email": , 
			"is_admin": bool,
			"level": int 
		}
	]
}

*/

namespace Model;

use Auth;
use Config;
use Mongo_Db;
use FuelException;

class GroupException extends \FuelException {}
class GroupNotFoundException extends \Model\GroupException {}

class Group extends \Model
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  string  Group collection
	 */
	protected static $collection = '';

	/**
	 * @var  array  Group array
	 */
	protected $group = array();

	/**
	 * @var  array  Group's team array
	 */
	protected $team = array();

	/**
	 * @var  array  Group's admins array
	 */
	protected $admins = array();

	/**
	 * Gets the collection names
	 */
	public static function _init()
	{
		static::$collection = strtolower(Config::get('auth.collection.groups'));

		static::$db = \Mongo_Db::instance();
	}

	/**
	 * Checks if the Field is set or not.
	 *
	 * @param   string  Field name
	 * @return  bool
	 */
	public function __isset($field)
	{
		return array_key_exists($field, $this->group);
	}

	/**
	 * Gets a field value of the group
	 *
	 * @param   string  Field name
	 * @return  mixed
	 * @throws  MontryGroupException
	 */
	public function __get($field)
	{
		return $this->get($field);
	}

	/**
	 * Gets all the group info.
	 *
	 * @param   string|int  Group id or name
	 * @return  void
	 */
	public function __construct($id = null)
	{
		if ($id === null)
		{
			return;
		}

		//query database for group
		$group = static::$db->get_where(static::$collection, array(
			'name' => $id
		), 1);

		// if there was a result - update user
		if (count($group) == 1)
		{
			$this->group = $group[0];
			$this->team = $group[0]['team'];
			$this->admins = $group[0]['admins'];
		}
		// group doesn't exist
		else
		{
			throw new \Model\GroupNotFoundException('group_not_found');
		}
	}

	/**
	 * Creates the given group.
	 *
	 * @param   array  Group info
	 * @return  int|bool
	 */
	public function create($group)
	{
		if ( ! array_key_exists('name', $group))
		{
			throw new \Model\GroupException('group_name_empty');
		}

		$slug = \Arr::get($group, 'slug', $group['name']);
		$group['slug'] = \Inflector::friendly_title($slug, '-', true);

		if (static::group_exists($group['slug']))
		{
			throw new \Model\GroupException('group_already_exists');
		}

		if ( ! array_key_exists('team', $group))
		{
			$group['team'] = array();
		}

		if ( ! array_key_exists('admins', $group))
		{
			$group['admins'] = array();
		}

		$result = static::$db->insert(static::$collection, $group);

		if(count($result) > 0)
		{
			return (string) $result;
		}

		return false;
	}

	/**
	 * Gets a given field (or array of fields).
	 *
	 * @param   string|array  Field(s) to get
	 * @return  mixed
	 * @throws  GroupException
	 */
	public function get($field = null)
	{
		// make sure a group id is set
		if (empty($this->group['_id']))
		{
			throw new \Model\GroupException('no_group_selected');
		}

		// if no fields were passed - return entire user
		if ($field === null)
		{
			return $this->group;
		}
		// if field is an array - return requested fields
		else if (is_array($field))
		{
			$values = array();

			// loop through requested fields
			foreach ($field as $key)
			{
				// check to see if field exists in group
				if (array_key_exists($key, $this->group))
				{
					$values[$key] = $this->group[$key];
				}
				else
				{
					throw new \Model\GroupException('not_found_in_group_object : '.$key);
				}
			}

			return $values;
		}
		// if single field was passed - return its value
		else
		{
			// check to see if field exists in group
			if (array_key_exists($field, $this->group))
			{
				return $this->group[$field];
			}

			throw new \Model\GroupException('not_found_in_group_object : '.$field);
		}
	}


	/**
	 * Update the given group
	 *
	 * @param   array  fields to be updated
	 * @return  bool
	 * @throws  GroupException
	 */
	public function update(array $fields)
	{
		// make sure a group id is set
		if (empty($this->group['_id']))
		{
			throw new \Model\GroupException('no_group_selected');
		}

		// init the update array
		$update = array();

		// update name
		if (array_key_exists('name', $fields) and $fields['name'] != $this->group['name'])
		{
			// make sure name does not already exist
			if (static::group_exists($fields['name']))
			{
				throw new \Model\GroupException('group_already_exists');
			}
			$update['name'] = $fields['name'];
			unset($fields['name']);
		}

		// update level
		if (array_key_exists('level', $fields))
		{
			$update['level'] = $fields['level'];
		}

		// update is_admin
		if (array_key_exists('is_admin', $fields))
		{
			$update['is_admin'] = $fields['is_admin'];
		}

		if (empty($update))
		{
			return true;
		}

		return static::$db
						->where(array('_id' => $this->group['_id']))
						->update(static::$collection, $update);

	}


	/**
	 * Delete's the current group.
	 *
	 * @return  bool
	 * @throws  GroupException
	 */
	public function delete()
	{
		// make sure a user id is set
		if (empty($this->group['id']))
		{
			throw new \Model\GroupException('no_group_selected');
		}

		$delete_group = self::$db
							->where(array('_id' => $this->group['_id']))
							->delete(static::$collection);

		if($delete_group )
		{
			// update user to null
			$this->group = array();

			return true;
		}
		return false;
	}

	/**
	 * Checks if the group exists
	 *
	 * @param   string|int  Group name|Group id
	 * @return  bool
	 */
	public static function group_exists($group)
	{
		$group_exists = static::$db
							->where(array('slug' => $group))
							->limit(1)
							->count(Config::get('auth.collection.groups'));

		return (bool) $group_exists;
	}

	/*
	 * Checks if the current user is part of the given group.
	 *
	 * @param   string user ID | email
	 * @param   string field to match
	 * @return  bool
	 */
	public function in_group($id, $field = 'email')
	{
		if($field == '_id' && !($id instanceof \MongoId))
		{
			$id = new \MongoId($id);
		}

		foreach ($this->team as $team)
		{
			if ($team[$field] == $id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Adds auser user to the group.
	 *
	 * @param   string|int  User ID or email
	 * @param   array  User's role in group
	 * @return  bool
	 * @throws  GroupException
	 */
	public function add_to_group($email, $role = array())
	{
		if ($this->in_group($email))
		{
			throw new \Model\GroupException('user_already_in_group');
		}

		try
		{
			$user = new User($email);
		}
		catch (UserNotFoundException $e)
		{
			throw new \Model\GroupException($e->getMessage());
		}

		$user_info = array(
				'_id'      => $user->get('_id'),
				'name'     => $user->get('name'),
				'email'     => $user->get('email'),
				'level'    => 0,
				'is_admin' => 0
			);

		$data = array_merge($user_info, $role);

		$update = array('$push' => array('team' => $data));

		$where = array('_id' => $this->group['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			$this->team[] = $data;

			return true;
		}
		return false;
	}

	/**
	 * Removes this user from the group.
	 *
	 * @param   string|int  Group ID or group name
	 * @return  bool
	 * @throws  GroupException
	 */
	public function remove_from_group($email)
	{
		if ( ! $this->in_group($email))
		{
			throw new \Model\GroupException('user_not_in_group');
		}

		try
		{
			$user = new User($email);
		}
		catch (UserNotFoundException $e)
		{
			throw new \Model\GroupException($e->getMessage());
		}

		$update = array('$pull' => array('team' => array('email' => $email)));

		$where = array('_id' => $this->group['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			foreach ($this->team as $team)
			{
				if ($team['email'] == $email)
				{
					unset($team);
				}
			}

			return true;
		}

		return false;
	}


	/**
	 * Grante user as admin for the group.
	 *
	 * @param   string User _ID
	 * @return  bool
	 * @throws  GroupException
	 */
	public function add_admin($email)
	{
		if ( ! $this->in_group($email))
		{
			throw new \Model\GroupException('user_not_in_group');
		}

		try
		{
			$user = new User($email);
		}
		catch (UserNotFoundException $e)
		{
			throw new \Model\GroupException($e->getMessage());
		}

		$update = array('$addToSet' => array('admins' => $user->get('_id')));

		$where = array('_id' => $this->group['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			$this->admins[] = $user->get('_id');

			return true;
		}
		return false;
	}

	/**
	 * Revoke user as admin for the group.
	 *
	 * @param   string User _ID
	 * @return  bool
	 * @throws  GroupException
	 */
	public function revoke_admin($id)
	{
		if ( ! $this->in_group($id))
		{
			throw new \Model\GroupException('user_not_in_group');
		}

		try
		{
			$user = new User($id);
		}
		catch (UserNotFoundException $e)
		{
			throw new \Model\GroupException($e->getMessage());
		}

		$update = array('$pull' => array('admins' => $user->get('_id')));

		$where = array('_id' => $this->group['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			if(!($id instanceof \MongoId))
			{
				$id = new \MongoId($id);
			}

			foreach ($this->admins as $admin)
			{
				if ($admin == $id)
				{
					unset($admin);
				}
			}

			return true;
		}

		return false;
	}

}

