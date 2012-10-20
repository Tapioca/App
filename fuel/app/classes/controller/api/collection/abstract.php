<?php

class Controller_Api_Collection_Abstract extends Controller_Api
{
	protected static $appslug;
	private static $namespace;

	public function before()
	{
		parent::before();

		static::$appslug   = $this->param('appslug', false);
		static::$namespace = $this->param('namespace', false);

		// check collection's namespace 
		// and app exists
		if( static::$appslug && !static::assignApp())
		{
			return;
		}

		// check if user is allowed
		// for this app
		static::isInApp();
	}

	public function get_index()
	{
		if( static::$granted )
		{
			try
			{
				$collection = Tapioca::collection( static::$app, static::$namespace );
			}
			catch( TapiocaException $e )
			{
				static::error($e->getMessage());
				return;
			}

			try
			{
				$documents      = Tapioca::document( static::$app, $collection );
				static::$data   = $documents->abstracts();
				static::$status = 200;
			}
			catch ( TapiocaException $e)
			{
				static::error($e->getMessage());
			}
		}
	}
}