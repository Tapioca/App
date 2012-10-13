<?php

class Controller_App extends Controller
{
	protected static $user;
	protected static $groups; 

	public function before()
	{
		if (Tapioca::check())
		{
			static::$user = Tapioca::user();

			$groups = static::$user->get('apps');

			
		}
		else
		{
			Response::redirect('log');
		}
	}
}