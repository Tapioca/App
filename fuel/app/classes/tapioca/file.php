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
	 * @var  object  Active group
	 */
	protected static $summary = null;

	/**
	 * @var  array  File's object
	 */
	protected $file = null;

	/**
	 * @var  string  file's name
	 */
	protected $filename = null;

	/**
	 * @var  string  root storage path
	 */
	protected static $storage = null;

	/**
	 * @var  string  app root storage path
	 */
	protected static $appStorage = null;

	/**
	 * @var  array  prefix for presets
	 */
	protected static $presets = array('', 'preview');

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

		static::$storage    = Config::get('tapioca.upload.storage');
		static::$appStorage = static::$storage.static::$group->get('slug');
		
		static::$db         = \Mongo_Db::instance();
		static::$gfs        = \GridFs::getFs(static::$db);

		static::$summary    = $this->get_summary();

		// if a Name was passed
		if ($filename)
		{
			$file =  static::$db
						->get_where(static::$collection, array(
							'filename' => $filename
						), 1);

			if(count($file) == 1)
			{
				$this->file     = $file[0];
				$this->filename = $filename;
			}
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

	public function get_summary()
	{
		$summary = static::$db
					->get_where(static::$collection, array(
						'type' => 'summary'
					), 1);

		if (count($summary) == 1)
		{
			return $summary[0];
		}

		return;
	}

	private function set_summary()
	{
		$summary = $this->get_summary();

		if (count($summary) == 0)
		{
			$fileTypes = Config::get('tapioca.file_types');
			$summary   = array(
					'type'   => 'summary',
					'presets' => array()
				);

			foreach ($fileTypes as $key => $value)
			{
				$summary[$key] = 0;
			}

			static::$db
				->where(array('type' => 'summary'))
				->update(static::$collection, $summary, array('upsert' => true));
		}
	}

	/**
	 * Increment/decrement total files per categories
	 *
	 * @param   string  category
	 * @param   int   Increment|Decrement
	 * @return  void
	 */
	public function inc_summary($category, $direction = 1)
	{
		$ret = static::$db->command(
					array('findandmodify' => static::$collection,
						  'query'         => array('type' => 'summary'),
						  'update'        => array('$inc' => array($category => (int) $direction)),
						  'new'           => true
					)
				);

		return (bool) ($ret['ok'] == 1);
	}

	/**
	 * apply preset to a file
	 *
	 * @param   string preset name
	 * @return  bool
	 */
	public function preset($preset_name)
	{
		if(is_null($this->filename))
		{
			throw new TapiocaFileException(__('tapioca.no_file_selected'));
		}

		if(in_array($preset_name, $this->file['presets']))
		{
			return true;
		}

		$summary = $this->get_summary();
		$presets = $summary['presets'];

		if(!isset($presets[$preset_name]))
		{
			return false;
		}

		$original_file = $this->get_path();
		$path          = $this->get_path(false);
		$new_file_path = $path.$preset_name.'-'.$this->filename;
		$resource      = \Image::load($original_file);
		
		$resource->config('presets', $presets);
		$resource->preset($preset_name)->save($new_file_path);

		if(file_exists($new_file_path))
		{
			$ret = static::$db
					->where(array(
						'filename' => $this->filename
					))
					->update(static::$collection, array(
						'$addToSet' => array(
							'presets' => $preset_name
						)
					), array(), true);

			return $ret;
		}
	}

	public function listing($category = null, $tag = null)
	{
		// exclude summary
		$where = array('type' => array( '$exists' => false ));

		if(!is_null($category))
		{
			$where['category'] = $category;
		}

		if(!is_null($tag))
		{
			$where['tags.key'] = $tag;
		}

		return static::$db
					->where($where)
					->get(static::$collection);
	}

	public function read()
	{
		if(is_null($this->file))
		{
			throw new TapiocaFileException(__('tapioca.no_file_selected'));
		}

		return $this->file;
/*
		$file =  static::$db
					->get_where(static::$collection, array(
						'filename' => $this->filename
					), 1);

		$this->file = $file[0];

		return $this->file;
*/
	}

	public function get_path($full_path = true, $preset = null)
	{
		$path = static::$appStorage.DIRECTORY_SEPARATOR.$this->file['category'].DIRECTORY_SEPARATOR;

		if(!$full_path)
		{
			return $path;
		}

		if($preset)
		{
			$path .= $preset.'-';
		}

		return $path.$this->filename;
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
	 * @param  object User instance
	 * @param  bool is Update 
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function save(\Auth\User $user, $update = false)
	{
		$files  = $this->upload();
		$result = array();

		foreach($files as &$file)
		{
			if(!isset($file['errors']))
			{
				try
				{
					$duplicate_name = $this->exists($file['filename'], $file['md5']);
				
					if($duplicate_name)
					{
						$file['basename'] = $file['basename'].'_'.time(); 
						$file['filename'] = strtolower($file['basename'].'.'.$file['extension']);
					}

					$ret = $this->create($file, $user, $update);
					
					$file_api    = '/api/'.static::$group->get('slug').'/file/'.$file['filename'];
					$file_url    = '/files/'.static::$group->get('slug').'/'.$file['category'].'/'.$file['filename'];
					// preview
					$preview_url = (strpos($file['mimetype'], 'image') !== false) ?
							'/files/'.static::$group->get('slug').'/'.$file['category'].'/preview-'.$file['filename'] : '';

					$result[] = array(
						'name'          => $file['filename'],
						'size'          => $file['length'],
						'url'           => $file_url,
						'thumbnail_url' => $preview_url,
						'delete_url'    => $file_api,
						'delete_type'   => 'DELETE'
					);

				}
				catch(TapiocaFileException $e)
				{
					$result[] = array(
						'error'         => $e->getMessage(),
						'name'          => $file['filename'],
						'size'          => $file['length'],
						'url'           => '',
						'thumbnail_url' => '',
						'delete_url'    => '',
						'delete_type'   => 'DELETE'
					);

					unlink($file['path']);
				}
			} // if isset error
			else
			{
				$result[] = array(
					'error'         => $file['errors'],
					'name'          => $file['filename'],
					'size'          => $file['length'],
					'url'           => '',
					'thumbnail_url' => '',
					'delete_url'    => '',
					'delete_type'   => 'DELETE'
				);
			}
		}

		return $result;
	}

	/**
	 * Create file
	 *
	 * @param   array  File description
	 * @return  object User instance
	 * @return  bool
	 * @throws  TapiocaException
	 */
	public function create(array &$fields, \Auth\User $user, $update = false)
	{
		$file_path = $fields['path'];
		$saved_as  = $fields['saved_as'];

		unset($fields['path']);
		unset($fields['saved_as']);

		$presets   = array();

		if(!is_null($this->filename) && $update)
		{
			// get previous file's data
			$previous = $this->read();

			// remove mongoID
			unset($previous['_id']);

			$fields['basename']  = $previous['basename'];
			$fields['filename']  = $previous['basename'].'.'.$fields['extension'];

			$presets = $previous['presets'];

			$this->delete(true);
		}

		$fields['uid'] = (string) static::$gfs
									->storeFile($file_path, array(
										'filename' => $fields['filename'],
										'appid'    => static::$group->get('id'),
										'category' => $fields['category']
									));

		$new_file = array(
			'ref'     => uniqid(),
			'created' => new \MongoDate(),
			'presets' => $presets,
			'user'    => array(
				'id'    => $user->get('id'),
				'name'  => $user->get('name'),
				'email' => $user->get('email'),
			)
		) + $fields;

		// do not work when upload multiple files
		$this->file      = $new_file;
		$this->filename  = $new_file['filename'];

		$ret = static::$db
				->where(array(
					'filename' => $new_file['filename']
				))
				->update(static::$collection, $new_file, array('upsert' => true));

		// if first file upload, create collection's summary
		$this->set_summary();

		if($ret & !$update)
		{
			$this->inc_summary($new_file['category']);
		}

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
		}

		$this->store($fields['filename'], $file_path, $fields['category']);	
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
		$cat_path     = static::$appStorage.DIRECTORY_SEPARATOR.$category;
		$file_path    = $cat_path.DIRECTORY_SEPARATOR.$filename;

		if(!is_dir(static::$appStorage))
		{
			File::create_dir(static::$storage, static::$group->get('slug'), 0755);			
		}

		if(!is_dir($cat_path))
		{
			File::create_dir(static::$appStorage, $category, 0755);			
		}

		if(file_exists($file_path))
		{
			File::delete($file_path);
		}

		File::copy($path, $file_path);

		unlink($path);
	}

	public function delete($soft = false)
	{
		if(is_null($this->filename))
		{
			throw new TapiocaFileException(__('tapioca.no_file_selected'));
		}

		if(isset($this->file['presets']))
		{
			static::$presets = array_merge(static::$presets, $this->file['presets']); 
		}

		$fileGfs  = static::$gfs
						->findOne(array(
							'filename' => $this->filename,
							'appid'    => static::$group->get('id')
						));

		if(count($fileGfs) > 0)
		{
			//Get the GridFS Object and Remove file
			static::$gfs->delete($fileGfs->file['_id']);

			// delete files + mongoDb data
			if(!$soft)
			{
				$cat_path = static::$appStorage.DIRECTORY_SEPARATOR.$this->file['category'];

				foreach(static::$presets as $preset)
				{
					$filename  = (empty($preset)) ? $this->filename : $preset.'-'.$this->filename;

					$file_path = $cat_path.DIRECTORY_SEPARATOR.$filename;

					File::delete($file_path);
				}

				$delete =  static::$db
							->where(array(
									'ref' => $this->file['ref']
							))
							->delete_all(static::$collection);

				if($delete)
				{
					$this->inc_summary($this->file['category'], -1);
				}

				$this->file     = null;
				$this->filename = null;
			}
		}
	}


	public function upload()
	{
		$config     = Config::get('tapioca.upload');
		$file_types = Config::get('tapioca.file_types');

		// process the uploaded files in $_FILES
		Upload::process($config);

		$result = array();

		// and process any errors
		foreach (Upload::get_errors() as $file)
		{
			$errorsMsg = array();

			foreach($file['errors'] as $error)
			{
				$errorsMsg[] = (isset($error['message'])) ? $error['message'] : $error['error'];
			}

			$errorsMsg = implode(' - ', $errorsMsg);

			$result[] = array(
							'errors'    => $errorsMsg,
							'filename'  => $file['name'],
							'length'    => $file['size']
						);
		}

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
			$key         = \Inflector::friendly_title(trim($value));
			$file_tags[] = array(
				'key'   => $key,
				'value' => trim($value)
			);
		}

		// if there are any valid files
		if (Upload::is_valid())
		{
			// save them according to the config
			Upload::save();

			foreach( Upload::get_files() as $file )
			{
				$file_path  = $file['saved_to'].$file['saved_as'];

				$basename	= \Inflector::friendly_title(trim(strtolower($file['filename'])));
				$filename   = $basename.'.'.$file['extension'];
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