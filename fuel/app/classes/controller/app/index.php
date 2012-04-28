<?php

class Controller_App_Index extends Controller_App
{
	public function action_index()
	{
		$tpl_data = array(
						'user' => array(
							'id'     => static::$user->get('id'),
							'groups' => array()
						)
					);


		// User Group
		$groups = static::$user->get('groups');

		foreach ($groups as $group)
		{
			$tpl_data['user']['groups'][] = $group['id'];
		}

		return View::forge('templates/app', $tpl_data);
	}
}