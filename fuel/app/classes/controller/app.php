<?php

class Controller_App extends Controller
{
	protected static $user;
	protected static $groups; 

	public function before()
	{
		if (Auth::check())
		{
			static::$user = Auth::user();

			$groups = static::$user->get('groups');

			
		}
		else
		{
			Response::redirect('log');
		}
	}
}