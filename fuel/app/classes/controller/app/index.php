<?php

class Controller_App_Index extends Controller_App
{
	public function action_index()
	{
		Tapioca::base();

		// get user's groups info
		$user_groups    = static::$user->get('groups');
		$user_groups_id = array();

		foreach($user_groups as $group)
		{
			$user_groups_id[] = $group['id'];
		}

		$groups = Auth::group()->read($user_groups_id);
		
		foreach($groups as $group)
		{
			$id = $group['id'];
			foreach($user_groups as &$user_group)
			{
				if($id == $user_group['id'])
				{
					$user_group = array_merge($user_group, $group);
					break; 
				}
			}
		}

		$app_settings = array(
							'base_uri' => str_replace(Uri::base(), '/', Uri::create('app/')), //Uri::current().'/',
							'user'     => array(
								'id'     => static::$user->get('id'),
								'groups' => $user_groups
							),
							'file'     => array(
								'base_path' => Config::get('tapioca.upload.public')
							)
						);

		return View::forge('templates/app', array('app_settings' => $app_settings));
	}
}