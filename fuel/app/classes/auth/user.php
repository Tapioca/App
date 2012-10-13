<?php

namespace Auth;

use Config;
use FuelException;

class UserException extends FuelException {}
class UserNotFoundException extends UserException {}

class User
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  array  User
	 */
	protected $user = array();

	/**
	 * @var  array  Groups
	 */
	protected $groups = array();

	/**
	 * @var  string  Collection name
	 */
	protected static $collection = null;

	/**
	 * @var  string  Login column string (formatted)
	 */
	protected $login_column_str = '';

	/**
	 * Loads in the user object
	 *
	 * @param   string  User id or Email value
	 * @return  void
	 * @throws  UserNotFoundException
	 */
	public function __construct($id = null, $check_exists = false)
	{
		// load and set config

		static::$collection = strtolower(Config::get('auth.collection.users'));

		static::$db = \Mongo_Db::instance();

		// if an ID was passed
		if ($id)
		{
			// make sure ID is a valid email
			if (filter_var($id, FILTER_VALIDATE_EMAIL))
			{
				// set field to login_column
				$field = 'email';
			}
			// if ID is not an email
			else
			{
				// set field to id for query
				$field = 'id';
			}

			//query database for user
			$user = static::$db->get_where(static::$collection, array(
				$field => $id
			), 1);

			// if there was a result - update user
			if (count($user) == 1)
			{
				// if just a user exists check - return true, no need for additional queries
				if ($check_exists)
				{
					return true;
				}

				$this->user = $user[0];
			}
			// user doesn't exist
			else
			{
				throw new \UserNotFoundException(__('auth.user_not_found'));
			}

			$this->groups = $user[0]['groups'];
		}
	}

	/**
	 * Checks if the Field is set or not.
	 *
	 * @param   string  Field name
	 * @return  bool
	 */
	public function __isset($field)
	{
		return array_key_exists($field, $this->user);
	}

	/**
	 * Gets a field value of the user
	 *
	 * @param   string  Field name
	 * @return  mixed
	 * @throws  UserException
	 */
	public function __get($field)
	{
		return $this->get($field);
	}


	/**
	 * Create's a new user.  Returns user '_id'.
	 *
	 * @param   array  User array for creation
	 * @return  int
	 * @throws  UserException
	 */
	public function create(array $user, $activation = false)
	{
		// check for required fields
		if (empty($user['email']) or empty($user['password']))
		{
			throw new \UserException(
				__('auth.email_and_password_empty')
			);
		}

		// check to see if login_column is already taken
		$user_exists = $this->user_exists($user['email']);

		if ($user_exists)
		{
			// check if account is not activated
			if ($activation and $user_exists['activated'] != 1)
			{
				// update and resend activation code
				$this->user = $user_exists;

				$hash = \Str::random('alnum', 24);

				$update = array(
					'activation_hash' => $hash
				);

				if ($this->update($update))
				{
					return array(
						'id'   => $this->user['id'],
						'hash' => base64_encode($user['email']).'/'.$hash
					);
				}
				return false;
			}

			throw new \UserException(__('auth.email_already_exists'));
		}

		$user_id = uniqid();

		// set new user values
		$new_user = array(
			'id' => $user_id,
			'email' => $user['email'],
			'password' => $this->generate_password($user['password']),
			'created_at' => new \MongoDate(),
			'activated' => ($activation) ? 0 : 1,
			'status' => 1,
			'remember_me' => null,
			'password_reset_hash' => null,
			'is_admin' => 0,
			'level' => 0,
			'groups' => array()
		) + $user;

		// set activation hash if activation = true
		if ($activation)
		{
			$hash = Str::random('alnum', 24);
			$new_user['activation_hash'] = $this->generate_password($hash);
		}

		// insert new user
		$result = static::$db->insert(static::$collection, $new_user);

		$insert_id = (string) $result;
		
		if($result)
		{
			// return activation hash for emailing if activation = true
			if ($activation)
			{
				// return array of id and hash
				return array(
					'id'   => $user_id,
					'hash' => base64_encode($user['email']).'/'.$hash
				);
			}

			return $user_id;
		}

		return false;
	}


	/**
	 * Gets a given field (or array of fields).
	 *
	 * @param   string|array  Field(s) to get
	 * @return  mixed
	 * @throws  UserException
	 */
	public function get($field = null)
	{
		// make sure a user id is set
		if (empty($this->user))
		{
			throw new \UserException(__('auth.no_user_selected_to_get'));
		}

		// if no fields were passed - return entire user
		if ($field === null)
		{
			return $this->user;
		}
		// if field is an array - return requested fields
		else if (is_array($field))
		{
			$values = array();

			// loop through requested fields
			foreach ($field as $key)
			{
				// check to see if field exists in user
				$val = \Arr::get($this->user, $key, '__MISSING_KEY__');
				
				if ($val !== '__MISSING_KEY__')
				{
					$values[$key] = $val;
				}
				else
				{
					throw new \UserException(
						__('auth.not_found_in_user_object', array('field' => $key))
					);
				}
			}

			return $values;
		}
		// if single field was passed - return its value
		else
		{
			// check to see if field exists in user
			$val = \Arr::get($this->user, $field, '__MISSING_KEY__');

			if ($val !== '__MISSING_KEY__')
			{
				return $val;
			}

			throw new \UserException(
				__('auth.not_found_in_user_object', array('field' => $field))
			);
		}
	}


	/**
	 * Update the current user
	 *
	 * @param   array  Fields to update
	 * @param   bool   Whether to hash the password
	 * @return  bool
	 * @throws  UserException
	 */
	public function update(array $fields, $hash_password = true)
	{
		// make sure a user id is set
		if (empty($this->user))
		{
			throw new \UserException(__('auth.no_user_selected'));
		}

		// init update array
		$update = array();

		// if updating email
		if (array_key_exists('email', $fields) and
			$fields['email'] != $this->user['email'] and
			$this->user_exists($fields['email']))
		{
			throw new \UserException(__('auth.email_already_exists'));
		}
		elseif (array_key_exists('email', $fields) and
				$fields['email'] == '')
		{
			throw new \UserException(__('auth.email_and_password_empty'));
		}
		elseif (array_key_exists('email', $fields))
		{
			$update['email'] = $fields['email'];
			unset($fields['email']);
		}

		// update password
		if (array_key_exists('password', $fields))
		{
			if (empty($fields['password']))
			{
				throw new \UserException(__('auth.email_and_password_empty'));
			}
			if ($hash_password)
			{
				$fields['password'] = $this->generate_password($fields['password']);
			}
			$update['password'] = $fields['password'];
			unset($fields['password']);
		}

		// update temp password
		if (array_key_exists('temp_password', $fields))
		{
			if ( ! empty($fields['temp_password']))
			{
				$fields['temp_password'] = $this->generate_password($fields['temp_password']);
			}
			$update['temp_password'] = $fields['temp_password'];
			unset($fields['temp_password']);
		}

		// update password reset hash
		if (array_key_exists('password_reset_hash', $fields))
		{
			if ( ! empty($fields['password_reset_hash']))
			{
				$fields['password_reset_hash'] = $this->generate_password($fields['password_reset_hash']);
			}
			$update['password_reset_hash'] = $fields['password_reset_hash'];
			unset($fields['password_reset_hash']);
		}

		// update remember me cookie hash
		if (array_key_exists('remember_me', $fields))
		{
			if ( ! empty($fields['remember_me']))
			{
				$fields['remember_me'] = $this->generate_password($fields['remember_me']);
			}
			$update['remember_me'] = $fields['remember_me'];
			unset($fields['remember_me']);
		}

		if (array_key_exists('activation_hash', $fields))
		{
			if ( ! empty($fields['activation_hash']))
			{
				$fields['activation_hash'] = $this->generate_password($fields['activation_hash']);
			}
			$update['activation_hash'] = $fields['activation_hash'];
			unset($fields['activation_hash']);
		}

		if (array_key_exists('last_login', $fields) and ! empty($fields['last_login']) and is_int($fields['last_login']))
		{
			$update['last_login'] = $fields['last_login'];
			unset($fields['last_login']);
		}

		if (array_key_exists('ip_address', $fields))
		{
			$update['ip_address'] = $fields['ip_address'];
			unset($fields['ip_address']);
		}

		if (array_key_exists('activated', $fields))
		{
			$update['activated'] = $fields['activated'];
			unset($fields['activated']);
		}

		if (array_key_exists('status', $fields))
		{
			$update['status'] = $fields['status'];
			unset($fields['status']);
		}

		$update = $update + $fields;

		if (empty($update))
		{
			return true;
		}

		// add update time
		$update['updated_at'] = new \MongoDate();

		$update_user = self::$db
						->where(array('_id' => $this->user['_id']))
						->update(self::$collection, $update);

		if ($update_user)
		{
			// change user values in object
			$this->user = $update + $this->user;

			return true;
		}

		return false;
	}


	/**
	 * Delete's the current user.
	 *
	 * @return  bool
	 */
	public function delete()
	{
		// make sure a user id is set
		if (empty($this->user))
		{
			throw new \UserException(__('auth.no_user_selected_to_delete'));
		}

		$delete_user = self::$db
						->where(array('_id' => $this->user['_id']))
						->delete(self::$collection);

		if($delete_user)
		{
			// update user to null
			$this->user = array();

			return true;
		}

		return false;
	}

	/**
	 * Enable a User
	 *
	 * @return  bool
	 * @throws  UserException
	 */
	public function enable()
	{
		if ($this->user['status'] == 1)
		{
			throw new \UserException(__('auth.user_already_enabled'));
		}
		return $this->update(array('status' => 1));
	}

	/**
	 * Disable a User
	 *
	 * @return  bool
	 * @throws  UserException
	 */
	public function disable()
	{
		if ($this->user['status'] == 0)
		{
			throw new \UserException(__('auth.user_already_disabled'));
		}
		return $this->update(array('status' => 0));
	}

	/**
	 * Check if user exists already
	 *
	 * @param   string  The Login Column value
	 * @return  bool
	 */
	protected function user_exists($id)
	{
		$query = (is_array($id)) ? $id : array('email' => $id);

		// query db to check for login_column
		$result = static::$db->get_where(static::$collection, $query, 1);

		if (count($result) == 1)
		{
			return $result[0];
		}

		return false;
	}


	/**
	 * Checks the given password to see if it matches the one in the database.
	 *
	 * @param   string  Password to check
	 * @param   string  Password type
	 * @return  bool
	 */
	public function check_password($password, $field = 'password')
	{
		// grabs the salt from the current password
		$salt = substr($this->user[$field], 0, 16);

		// hash the inputted password
		$password = $salt.$this->hash_password($password, $salt);

		// check to see if passwords match
		return $password == $this->user[$field];
	}


	/**
	 * Generates a random salt and hashes the given password with the salt.
	 * String returned is prepended with a 16 character alpha-numeric salt.
	 *
	 * @param   string  Password to generate hash/salt for
	 * @return  string
	 */
	protected function generate_password($password)
	{
		$salt = \Str::random('alnum', 16);

		return $salt.$this->hash_password($password, $salt);
	}

	/**
	 * Hash a given password with the given salt.
	 *
	 * @param   string  Password to hash
	 * @param   string  Password Salt
	 * @return  string
	 */
	protected function hash_password($password, $salt)
	{
		$password = hash('sha256', $salt.$password);

		return $password;
	}


	/**
	 * Changes a user's password
	 *
	 * @param   string  The new password
	 * @param   string  Users old password
	 * @return  bool
	 * @throws  UserException
	 */
	public function change_password($password, $old_password)
	{
		// make sure old password matches the current password
		if ( ! $this->check_password($old_password))
		{
			throw new \UserException(__('auth.invalid_old_password'));
		}

		return $this->update(array('password' => $password));
	}


	/**
	 * Returns an array of groups the user is part of.
	 *
	 * @return  array
	 */
	public function groups()
	{
		return $this->groups;
	}

	/**
	 * Adds this user to the group.
	 *
	 * @param   string|int  Group ID or group name
	 * @param   array  User's role in group
	 * @return  bool
	 * @throws  UserException
	 */
	public function add_to_group($id, $role = array())
	{
		if ($this->in_group($id))
		{
			throw new \UserException(__('auth.user_already_in_group'));
		}

		try
		{
			$group = new Group($id);
		}
		catch (GroupNotFoundException $e)
		{
			throw new \UserException($e->getMessage());
		}

		$group_info = array(
				'id'       => $group->get('id'),
				'name'     => $group->get('name'),
				'slug'     => $group->get('slug'),
				'level'    => 0,
				'is_admin' => 0
			);

		$data = array_merge($group_info, $role);

		$update = array('$push' => array('groups' => $data));

		$where = array('_id' => $this->user['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($query)
		{
			$this->groups[] = $data;

			return true;
		}
		return false;
	}

	/**
	 * Update user level in a specific group.
	 *
	 * @param   string  Group ID
	 * @param   array  User's role in group
	 * @return  bool
	 * @throws  UserException
	 */
	public function update_group_status($id, $role = array())
	{
		if (!$this->in_group($id))
		{
			throw new \UserException(__('auth.user_not_in_group'));
		}

		try
		{
			$group = new Group($id);
		}
		catch (GroupNotFoundException $e)
		{
			throw new \UserException($e->getMessage());
		}

		foreach($this->groups as &$group)
		{
			if($group['id'] == $id)
			{
				$group = array_merge($group, $role);				
			}
		}

		$update = array('groups' => $this->groups);

		$where = array('_id' => $this->user['_id']);

		$query = static::$db
					->where($where)
					->update(static::$collection, $update);

		if($query)
		{
			return true;
		}
		return false;
	}

	/**
	 * Removes this user from the group.
	 *
	 * @param   string|int  Group ID or group name
	 * @return  bool
	 * @throws  UserException
	 */
	public function remove_from_group($id)
	{
		if ( ! $this->in_group($id))
		{
			throw new \UserException(__('auth.user_not_in_group'));
		}

		try
		{
			$group = new Group($id);
		}
		catch (GroupNotFoundException $e)
		{
			throw new \UserException($e->getMessage());
		}

		$query = (is_array($id)) ? $id : array('id' => $id);

		$update = array('$pull' => array('groups' => $query));

		$where = array('_id' => $this->user['_id']);

		$remove = static::$db
					->where($where)
					->update(static::$collection, $update, array(), true);

		if($remove)
		{
			$val = current($query);
			$key = key($query);

			foreach ($this->groups as $group)
			{
				if ($group[$key] == $val)
				{
					unset($group);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Checks if the current user is part of the given group.
	 *
	 * @param   string|array  Group ID or specific filed
	 * @return  bool
	 */
	public function in_group($query)
	{
		$query = (is_array($query)) ? $query : array('id' => $query);
		$val   = current($query);
		$key   = key($query);

		foreach ($this->groups as $group)
		{
			if ($group[$key] == $val)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the user is an admin
	 *
	 * @return  bool
	 */
	public function is_admin()
	{
		return (bool) $this->user['is_admin'];
	}

	/**
	 * Checks if the user has the given level
	 *
	 * @param   int  Level to check
	 * @return  bool
	 */
	public function has_level($level)
	{
		if ($this->user['level'] == $level)
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the user has at least given level
	 *
	 * @param   int  Level to check
	 * @return  bool
	 */
	public function atleast_level($level)
	{
		if ($this->user['level'] >= $level)
		{
			return true;
		}

		return false;
	}

	/**
	 * Grante user as admin.
	 *
	 * @param   int  admin level 
	 * @return  bool
	 * @throws  UserException
	 */
	public function granted_admin(int $level)
	{
		return $this->update(array('is_admin' => 1, 'level' => $level));
	}

	/**
	 * Revoke user as admin.
	 *
	 * @return  bool
	 * @throws  UserException
	 */
	public function revoke_admin()
	{
		return $this->update(array('is_admin' => 0));
	}

	/**
	 * Query the database to check if admin has been created.
	 *
	 * @return  bool
	 */
	public function admin_set()
	{
		$admin = static::$db->get_where(static::$collection, array(
			'is_admin' => 1
		), 1);

		return (bool) count($admin);
	}
}
