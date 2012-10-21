<?php

class Controller_Api_Library extends Controller_Api
{
	protected static $appslug;
	private static $file;
	private static $filename;

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

		if(  !is_null( $filename ) &&  !is_null( $extension ))
		{
			static::$filename = $filename.'.'.$extension;
		}

		if( static::$filename )
		{
			try
			{
				static::$file = Tapioca::library( static::$app, static::$filename );
			}
			catch( \TapiocaException $e)
			{
				static::error( $e->getMessage() );
			}
		}
		else
		{
			static::$file = Tapioca::library( static::$app );
		}
	}

	// get file listing
	public function get_index()
	{
		if( static::$granted )
		{
			if( !static::$filename )
			{
				// filters
				$category = Input::get('category', null);
				$tag      = Input::get('tag', null);

				$ret  = static::$file->listing( $category, $tag );				
			}
			else
			{
				$ret  = static::$file->read();
			}

			static::$data   = $ret;
			static::$status = 200;
		}
	}

	// add a file to the library
	// OR update file
	public function post_index()
	{
		if( static::$granted )
		{
			$update = (bool) static::$filename;
			
			static::$data   = static::$file->save( static::$user, $update );
			static::$status = 200;
		} 
	}

	// update a file meta
	public function put_index()
	{
		if( static::$granted && static::$filename )
		{
			$meta = Input::json();

			static::$data   = static::$file->update( static::$user, $meta );
			static::$status = 200;
		} 
	}

	public function delete_index()
	{
		if( static::$granted && static::$filename && static::isAppAdmin() )
		{
			if( ! static::deleteToken( 'library', static::$filename ))
			{
				return;
			}

			$list = static::$file->delete();

			static::$data   = array('status' => 'ok');
			static::$status = 200;
		} // if granted
	}
}