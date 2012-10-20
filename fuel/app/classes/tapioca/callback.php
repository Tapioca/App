<?php

namespace Tapioca;

use FuelException;
use Config;

class CallbackException extends FuelException {}

class Callback
{
	private static $namespace;
	private static $slug;
	private static $events;
	private static $callbacks = null;

	public static function register(App $app, $collection)
	{
		if( count($collection['callback']) > 0)
		{
			static::$callbacks = $collection['callback'];
			static::$slug      = $app->get('slug');
			static::$namespace = ucfirst($group->get('slug'));

			\Module::load(static::$slug);

// \Debug::show($collection['callback']);
// call_user_func_array('\\'.static::$namespace .'\\'.$collection['callback']['before'][0], array('Hannes'));
//\Bdn\Season::interval('params');

		}
	}

	public static function trigger($event, &$data)
	{
		if(isset(static::$callbacks[$event]))
		{
			foreach(static::$callbacks[$event] as $cb)
			{
				$data = call_user_func_array('\\'.static::$namespace .'\\'.$cb, array($data));
			}
		}
	}

	public static function reset()
	{
		static::$callbacks = null;
	}
}