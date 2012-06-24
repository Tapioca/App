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

		// status translation, to move somewhere else
		$statusArray  = Config::get('tapioca.status');
		$statusTech   = array();
		$statusPublic = array();
		foreach ($statusArray as $row)
		{
			$statusTech[$row[0]] = array(
				'label' => __('tapioca.doc_status.'.$row[1]),
				'class' => $row[2]
			);

			if($row[0] >= 0)
			{
				$statusPublic[] = array(
					'value' => $row[0],
					'label' => $row[1],
					'class' => $row[2]
				);
			}
		}

		$app_settings = array(
							'base_uri' => str_replace(Uri::base(), '/', Uri::create('app/')), //Uri::current().'/',
							'api_uri'  => str_replace(Uri::base(), '/', Uri::create('api/')), //Uri::current().'/',
							'root_uri' => str_replace(Uri::base(), '/', Uri::create('/')), //Uri::current().'/',
							'user'     => array(
								'id'     => static::$user->get('id'),
								'groups' => $user_groups
							),
							'file'     => array(
								'base_path' => Config::get('tapioca.upload.public')
							),
							'status'   => array(
								'public' => $statusPublic,
								'tech'   => $statusTech
							)
						);
		
		return View::forge('templates/app', array('app_settings' => $app_settings));
	}
}