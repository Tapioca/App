<?php

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
	 * Gets the collection names
	 */
	public static function _init()
	{
		static::$collection = strtolower(Config::get('auth.collection.groups'));

		static::$db = \Mongo_Db::instance();
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

		if (static::group_exists($group['name']))
		{
			throw new \Model\GroupException('group_already_exists');
		}

		if ( ! array_key_exists('level', $group))
		{
			throw new \Model\GroupException('group_level_empty');
		}

		if ( ! array_key_exists('is_admin', $group))
		{
			$group['is_admin'] = 0;
		}

		if ( ! array_key_exists('parent', $group))
		{
			$group['parent'] = 0;
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
							->where(array('name' => $group))
							->limit(1)
							->count(Config::get('auth.collection.groups'));

		return (bool) $group_exists;
	}

}

