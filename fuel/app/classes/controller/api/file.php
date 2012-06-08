<?php

class Controller_Api_File extends Controller_Api
{
	private static $filename;
	private static $presset;
	private static $query;
	private static $category;
	private static $tag;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		static::$filename  = $this->param('filename', null);
		static::$presset   = $this->param('presset', null);
		static::$query     = \Input::json('q', null);
		static::$category  = \Input::get('category', null);
		static::$tag       = \Input::get('tag', null);

		$extension         = \Input::extension();

		if(!is_null($extension))
		{
			static::$filename = static::$filename.'.'.$extension;
		}
	}

	/* Summary
	----------------------------------------- */

	// get file listing
	public function get_summary()
	{
		if(self::$granted)
		{
			$file    = Tapioca::file(self::$group);

			self::$data   = $file->get_summary();
//\Debug::show(self::$data);
//exit;
			self::$status = 200;
		} // if granted
	}

	/* Data
	----------------------------------------- */

	// get file listing
	public function get_index()
	{
		if(self::$granted)
		{
			$file = Tapioca::file(self::$group);
			$list = $file->listing(static::$category, static::$tag);
//\Debug::show($list);
//exit;
			self::$data   = $list;
			self::$status = 200;
		} // if granted
	}

	// add a file to the library.
	public function post_index()
	{
		if(self::$granted)
		{
			$file = Tapioca::file(self::$group);
			$list = $file->save(self::$user);
//\Debug::show($list);
//exit;
			self::$data   = $list;
			self::$status = 200;
		} // if granted
	}

	/* File
	----------------------------------------- */

	// get a specific file
	public function get_name()
	{
		if(self::$granted)
		{
			$file = Tapioca::file(self::$group, static::$filename); //->read();

			self::$data   = $file;
			self::$status = 200;
		} // if granted
	}

	// update a file.
	public function post_name()
	{
		if(self::$granted)
		{
			$file = Tapioca::file(self::$group, static::$filename);
			$list = $file->save(self::$user, true);
\Debug::show($list);
exit;
			self::$data   = $list;
			self::$status = 200;
		} // if granted
	}

	public function delete_name()
	{
		if(self::$granted)
		{
			$file = Tapioca::file(self::$group, static::$filename);
			$list = $file->delete();

			self::$data   = array('ok');
			self::$status = 200;
		} // if granted
	}

	/* Summary
	----------------------------------------- */

	// get file listing
	public function get_preset()
	{
		if(self::$granted)
		{
			$file    = Tapioca::file(self::$group, static::$filename);
			$file->preset(static::$preset_name);
			
			self::$data   = $file->get_summary();
			self::$status = 200;
		} // if granted
	}
}