<?php

class Controller_App_Index extends Controller_App
{
	public function action_index()
	{
		Tapioca::base();
		
		$tpl_data = array(
						'user' => array(
							'id'     => static::$user->get('id'),
							'groups' => static::$user->get('groups')
						)
					);

		return View::forge('templates/app', $tpl_data);
	}
}