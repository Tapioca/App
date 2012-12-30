<?php

namespace Tapioca;

use FuelException;
use Config;

class HookException extends FuelException {}

class Hook
{
	private static $namespace;
	private static $slug;
	private static $events;
	private static $hooks = null;

	public static function register(App $app, $collection)
	{
		if( !empty( $collection['hooks'] ) )
		{
			static::$hooks     = $collection['hooks'];
			static::$slug      = $app->get('slug');
			static::$namespace = ucfirst( static::$slug );

			\Module::load(static::$slug);
		}
	}

	public static function trigger($event, &$data, $status = null)
	{
		if( isset( static::$hooks[$event] ) )
		{
			foreach( static::$hooks[$event] as $cb)
			{
				$data = call_user_func_array('\\'.static::$namespace .'\\'.$cb, array($data, $status));
			}
		}
	}

	public static function reset()
	{
		static::$hooks = null;
	}
}