<?php

namespace Tapioca;

use FuelException;
use Config;
use Upload;

class TapiocaFileException extends FuelException {}

class File
{
	/**
	 * @var  string  Database instance
	 */
	protected static $db = null;

	/**
	 * @var  string  GridFs instance
	 */
	protected static $gfs = null;

	/**
	 * @var  string  MongoDb collection's name
	 */
	protected static $collection = null;

	/**
	 * @var  object  Active group
	 */
	protected static $group = null;

	/**
	 * @var  array  File's object
	 */
	protected $file = null;

	/**
	 * @var  string  file's name
	 */
	protected static $filename = null;

	/**
	 * @var  array Errors list
	 */
	protected $errors = null;

	/**
	 * Loads in the File object
	 *
	 * @param   string  Group id
	 * @param   string  Collection namespace
	 * @param   string  Document ref
	 * @return  void
	 */
	public function __construct(\Auth\Group $group, $filename = null, $check_exists = false)
	{
		// load and set config
		static::$group      = $group;
		static::$collection = static::$group->get('slug').'--files';
		
		static::$db  = \Mongo_Db::instance();
		static::$gfs = \GridFs::getFs(static::$db);

		// if a Name was passed
		if ($filename)
		{
			static::$filename = $filename;
		}
	}

	/**
	 * Magic get method to allow getting class properties but still having them protected
	 * to disallow writing.
	 *
	 * @return  mixed
	 */
	public function __get($property)
	{
		return $this->$property;
	}


	/**
	 * List of files to create/update
	 *
	 * @return  object User instance
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function save(\Auth\User $user)
	{
		$files = $this->upload();

		foreach($files as &$file)
		{
			try
			{
				if($this->exists($file['filename'], $file['md5']))
				{
					$file['basename'] = $file['basename'].'_'.time(); 
					$file['filename'] = strtolower($file['basename'].'.'.$file['extension']);
				}			
			}
			catch(TapiocaFileException $e)
			{
				$error = array(
					'filename' => $file['filename'],
					'errors'   => array(
						'error'   => 'XXX',
						'message' => $e->getMessage()
					)
				);

				$this->errors[] = $error;

				unset($file);
			}
		}

		if(count($files) > 0)
		{
			$result = array();

			foreach($files as $file)
			{
				$ret = $this->create($file, $user);
				
				$file_url = '/api/'.static::$group->get('slug').'/file/'.$file['filename'];

				$result[] = array(
					'name'          => $file['filename'],
					'size'          => $file['length'],
					'url'           => $file_url,
					'thumbnail_url' => "/example.org/thumbnails/picture1.jpg",
					'delete_url'    => $file_url,
					'delete_type'   => 'DELETE'
				);
			}

			return $result;
		}
	}

	/**
	 * Create file
	 *
	 * @param   array  File description
	 * @return  object User instance
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function create(array $fields, \Auth\User $user)
	{
		$file_path = $fields['path'];
		unset($fields['path']);


		$fields['uid'] = (string) static::$gfs
									->storeFile($file_path, array(
										'filename' => $fields['filename'],
										'appid'    => static::$group->get('id')
									));

		$new_file = array(
			'created' => new \MongoDate(),
			'user'    => array(
				'id'    => $user->get('id'),
				'name'  => $user->get('name'),
				'email' => $user->get('email'),
			)
		) + $fields;

		// do not work whem upload multiple files
		$this->file      = $new_file;
		$this->namespace = $new_file['filename'];

		return static::$db->insert(static::$collection, $new_file);
	}

	/**
	 * Check if a file as the same name OR if the same file exist (md5)
	 *
	 * @param   string file name
	 * @return  string file md5
	 * @return  bool
	 * @throws  TapiocaFileException
	 */
	private function exists($filename, $md5)
	{
		// query db to check for filename
		$result = static::$db->get_where(static::$collection, array(
											'filename' => $filename,
											'md5'      => $md5
										));

		if (count($result) > 0)
		{
			foreach ($result as $file)
			{
				if($file['md5'] == $md5)
				{
					throw new TapiocaFileException(
						__('tapioca.file_already_exists', array('name' => $filename))
					);
				}

				if($file['filename'] == $filename)
				{
					return true;
				}
			}
		}

		return false;
	}


	public function upload()
	{
		$config     = Config::get('tapioca.upload');
		$file_types = Config::get('tapioca.file_types');

		// process the uploaded files in $_FILES
		Upload::process($config);

		// and process any errors
		foreach (Upload::get_errors() as $file)
		{
			$error = array(
				'filename' => $file['name'],
				'errors'   => $file['errors']
			);

			$this->errors[] = $error;

			//\Debug::show($file);
		}

		$result = array();

		$tags = \Input::post('tags', null);

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
			$key       = \Inflector::friendly_title(trim($value));
			$file_tags = array(
				'key'   => $key,
				'value' => trim($value)
			);
		}

		// if there are any valid files
		if (Upload::is_valid())
		{
			// save them according to the config
			Upload::save();

			$finfo = new \finfo(FILEINFO_MIME);

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
								'path'      => $file_path,
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

				$result[] = $new_file;
			}
		}

		return $result;
	}
}