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
	protected $filename = null;

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

			$saved_to     = Config::get('tapioca.upload.path');
			$preview_path = $saved_to.'/preview-'.strtolower($saved_as);
			
			static::$gfs
				->storeFile($preview_path, array(
					'filename' => $fields['filename'],
					'appid'    => static::$group->get('id'),
					'preview'  => true
				));
			
			unlink($file_path);
			unlink($preview_path);
		}
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
								'saved_as'  => $file['saved_as'],
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