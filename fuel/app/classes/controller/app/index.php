<?php

class Controller_App_Index extends Controller_App
{
	public function action_index()
	{
		Tapioca::base();
		
		$app_settings = array(
							'base_uri' => Uri::current().'/',
							'user'     => array(
								'id'     => static::$user->get('id'),
								'groups' => static::$user->get('groups')
							)
						);

		return View::forge('templates/app', array('app_settings' => $app_settings));
	}
}