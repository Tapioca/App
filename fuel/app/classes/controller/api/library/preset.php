<?php

class Controller_Api_Library_Preset extends Controller_Api
{
	protected static $appslug;
	private static $file;

	public function before()
	{
		parent::before();

		static::$appslug    = $this->param('appslug', false);

		// check app exists
		if( static::$appslug && !static::assignApp() )
		{
			return;
		}

		// filename
		$filename  = $this->param('filename', null);
		$extension = Input::extension();

		if( is_null( $filename ) )
		{
			static::error( __('tapioca.missing_required_params'));
			return;
		}

		try
		{
			static::$file = Tapioca::library( static::$app, $filename );
		}
		catch( \TapiocaException $e)
		{
			static::error( $e->getMessage() );
		}
	}

	public function post_index()
	{
		if( static::$granted )
		{
			$preset = $this->param('preset', null);

			try
			{
				$ret = static::$file->preset( $preset );

				if( $ret )
				{
					static::$data   = static::$file->read();
					static::$status = 200;				
				}
				else
				{
					static::error( __('tapioca.internal_server_error') );
				}
			}
			catch( \TapiocaException $e)
			{
				static::error( $e->getMessage() );
			}
		} 
	}
}