<?php

namespace Tapioca;

use FuelException;
use Config;
use Upload;
use File;

class TapiocaFileException extends FuelException {}

class Files
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
	protected $filename = null;

	/**
	 * @var  array Errors list
	 */
	protected $errors = array();

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
			$this->filename = $filename;
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

	public function read()
	{
		if(is_null($this->filename))
		{
			throw new TapiocaFileException(__('tapioca.no_file_selected'));
		}

		$file =  static::$db->get_where(static::$collection, array(
							'filename' => $this->filename
						), 1);

		$this->file = $file[0];

		return $this->file;
	}

	public function getBytes($preview = false)
	{
		if(is_null($this->file))
		{
			$this->read();
			//throw new TapiocaFileException(__('tapioca.no_file_selected'));
		}
		
		$query = array( 'filename' => $this->filename,
						'appid'    => static::$group->get('id'));

		$query['preview'] = ($preview) ? true : array( '$exists' => false );

		$cursor = static::$gfs
					->find($query);

		$result = array();

		foreach($cursor as $c)
		{
			$result[] = $c;
		}
		return $result;
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
				$duplicate_name = $this->exists($file['filename'], $file['md5']);
			
				if($duplicate_name)
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
				if(!\Arr::in_array_recursive($file['filename'], $this->errors))
				{
					$ret = $this->create($file, $user);
					
					$file_api    = '/api/'.static::$group->get('slug').'/file/'.$file['filename'];
					$file_url    = '/file/'.static::$group->get('slug').'/'.$file['filename'];
					// preview
					$preview_url = (strpos($file['mimetype'], 'image') !== false) ?
							'/file/'.static::$group->get('slug').'/preview/'.$file['filename'] : '';

					$result[] = array(
						'name'          => $file['filename'],
						'size'          => $file['length'],
						'url'           => $file_url,
						'thumbnail_url' => $preview_url,
						'delete_url'    => $file_api,
						'delete_type'   => 'DELETE'
					);
				}
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
		$saved_as  = $fields['saved_as'];

		unset($fields['path']);
		unset($fields['saved_as']);

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
		$this->filename  = $new_file['filename'];

		$ret = static::$db->insert(static::$collection, $new_file);

		// preview
		if($ret && strpos($fields['mimetype'], 'image') !== false)
		{
			\Image::load($file_path)
				->config('bgcolor', null)
				->config('quality', 60)
				->config('filetype', 'png')
				->crop_resize(100, 100)
				->save_pa('preview-');

			$saved_to        = Config::get('tapioca.upload.path');
			$preview_tmpname = 'preview-'.$saved_as;
			$preview_name    = 'preview-'.$fields['filename'];
			$preview_path    = $saved_to.DIRECTORY_SEPARATOR.$preview_tmpname;

			$this->store($preview_name, $preview_path, $fields['category']);	
			unlink($preview_path);
		}

		$this->store($fields['filename'], $file_path, $fields['category']);	
		unlink($file_path);
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
		$result = static::$db
						->or_where(array(
							'filename' => $filename,
							'md5'      => $md5
						))
						->get(static::$collection);

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

	private function store($filename, $path, $category)
	{
		$storage_path = Config::get('tapioca.upload.storage');
		$app_slug     = static::$group->get('slug');

		$app_path     = $storage_path.$app_slug;
		$cat_path     = $app_path.DIRECTORY_SEPARATOR.$category;

		if(!is_dir($app_path))
		{
			\File::create_dir($storage_path, $app_slug, 0755);			
		}

		if(!is_dir($cat_path))
		{
			\File::create_dir($app_path, $category, 0755);			
		}

		\File::copy($path, $cat_path.DIRECTORY_SEPARATOR.$filename);
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

			//$finfo = new \finfo(FILEINFO_MIME);

			foreach( Upload::get_files() as $file )
			{
				$file_path  = $file['saved_to'].$file['saved_as'];

				//$minetype	= explode(';', $finfo->file($file_path));
				$basename	= \Inflector::friendly_title(trim(strtolower($file['filename'])));
				$filename   = $basename.'.'.$file['extension'];

//				$finfo->file($file_path);

				$category   = 'other';
				foreach ($file_types as $key => $values)
				{
					if(in_array($file['mimetype'], $values))
					{
						$category = $key;
					}
				}

				$new_file = array(
								'saved_as'  => $file['saved_as'],
								'path'      => $file_path,
								'mimetype'  => $file['mimetype'],
//								'charset'   => str_replace('charset=', '', trim($minetype[1])),
								'extension' => $file['extension'],
								'basename'  => $basename,
								'filename'  => $filename,
								'length'    => $file['size'],
								'md5'       => md5_file($file_path),
								'tags'      => $file_tags,
								'category'  => $category
							);

				// if file is an image, we get width/height
				if(strpos($file['mimetype'], 'image') !== false)
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