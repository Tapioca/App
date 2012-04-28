<?php

class Controller_Api_Group extends Controller_Api
{
	public function get_index()
	{
		if(self::$granted)
		{
			// call /api/group return current user's groups
			if(is_null(static::$group))
			{
				$group_info = static::$user->get('groups');
			}
			else // call /api/app-slug/group return group's info
			{
				$group_info = self::$group->get();

				unset($group_info['_id']);
			}

			self::$data   = $group_info;
			self::$status = 200;
		}
	}

	//create new group.
	public function post_index()
	{
		if(self::$granted)
		{
			$fields = array(
		        'name'  => 'Group Alpha'
		    );

			// create a group
			try
			{
				$group_id = Auth::group()->create($fields);
				
				if($group_id)
				{
					static::$group = Auth::group($group_id);

					// Create group's admin
					$admin_email = static::$user->get('email');

					static::$group->add_to_group($admin_email, array('is_admin' => 1, 'level' => 100));
					static::$group->add_admin($admin_email);

					static::$user->add_to_group($group_id);

					self::$data   = static::$group->get();
					self::$status = 200;
				}
			}
			catch (GroupException $e)
			{
				static::error($e->getMessage());
			}
		}
	}
}