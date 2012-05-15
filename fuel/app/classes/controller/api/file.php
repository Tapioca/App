<?php

class Controller_Api_File extends Controller_Api
{
	private static $filename;
	private static $presset;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		static::$filename = $this->param('filename', null);
		static::$presset  = $this->param('presset', null);
	}

	/* Data
	----------------------------------------- */

	public function get_index()
	{
		if(self::$granted)
		{
			$file = Tapioca::file(self::$group);

			self::$data   = array();
			self::$status = 200;
		} // if granted
	}

	//create collection data.
	public function post_index()
	{
		if(self::$granted)
		{
			$file = Tapioca::file(self::$group);

			$list = $file->save(self::$user);
Debug::show($file->errors);
Debug::show($list);
exit;
			self::$data   = array('ok');
			self::$status = 200;
		} // if granted
	}

	//update collection data.
	public function put_index()
	{
		if(self::$granted)
		{
			self::$data   = array();
			self::$status = 200;
		} // if granted
	}

	public function delete_index()
	{
		if(self::$granted)
		{
			self::$data   = array();
			self::$status = 200;
		} // if granted
	}
/*
	public function upload()
	{
		$config     = Config::get('tapioca.upload');
		$file_types = Config::get('tapioca.file_types');

		// process the uploaded files in $_FILES
		Upload::process($config);
		
		$result = array();

		$tags = Input::post('tags', null);

		if(!is_array($tags))
		{
			$tags = trim($tags);

			if(substr($tags, -1) == ',')
			{
				$tags = rtrim($tags, ',');
			}
			
			$tags = array_filter(explode(',', $tags));
		}

		$file_tags = array();

		foreach ($tags as $value)
		{
			$key             = \Inflector::friendly_title(trim($value));
			$file_tags[$key] = trim($value);
		}

		// if there are any valid files
		if (Upload::is_valid())
		{
			// save them according to the config
			Upload::save();

			$finfo = new finfo(FILEINFO_MIME);

			foreach( Upload::get_files() as $file )
			{
				$file_path  = $file['saved_to'].$file['saved_as'];

				$minetype	= explode(';', $finfo->file($file_path));
				$basename	= \Inflector::friendly_title(trim(strtolower($file['filename'])));
				$filename   = $basename.'.'.$file['extension'];

				$finfo->file($file_path);

				$category   = '_other_';
				foreach ($file_types as $key => $values)
				{
					if(in_array($file['mimetype'], $values))
					{
						$category = $key;
					}
				}

				$new_file = array(
								'mimetype'  => $file['mimetype'],
								'charset'   => str_replace('charset=', '', trim($minetype[1])),
								'extension' => $file['extension'],
								'basename'  => $basename,
								'filename'  => $filename,
								'length'    => $file['size'],
								'md5'       => md5_file($file_path),
								'tags'      => $file_tags,
								'category'  => $category
							);

				// if file is an image, we get width/height
				if(strpos($minetype[0], 'image') !== false)
				{
					$new_file['size'] = array();
					list($new_file['size']['width'], $new_file['size']['height']) = getimagesize($file_path);
				}

				$result[] = array(
					'name'          => $filename,
					'size'          => $file['size'],
					'url'           => "/example.org/files/picture1.jpg",
					'thumbnail_url' => "/example.org/thumbnails/picture1.jpg",
					'delete_url'    =>  "/example.org/upload-handler?file=picture1.jpg",
					'delete_type'   => 'DELETE'
				);
			}
		}

		// and process any errors
		foreach (Upload::get_errors() as $file)
		{
			\Debug::show($file);
		    // $file is an array with all file information,
		    // $file['errors'] contains an array of all error occurred
		    // each array element is an an array containing 'error' and 'message'
		}

		return $result;
	}
*/
}