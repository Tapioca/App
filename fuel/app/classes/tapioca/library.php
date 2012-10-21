<?php

namespace Tapioca;

use FuelException;
use Config;
use Upload;
use File;

class LibraryException extends FuelException {}

class Library
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
	protected static $dbCollectionName = null;

	/**
	 * @var  object  Active App
	 */
	protected static $app = null;

	/**
	 * @var  bool  Library resume
	 */
	protected static $summary = false;

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
	 * @param   string  App instance
	 * @param   string  Filename
	 * @return  void
	 */
	public function __construct(App $app, $filename = null)
	{
		// load and set config
		static::$app              = $app;
		static::$dbCollectionName = static::$app->get('slug').'--library';

		static::$storage    = Config::get('tapioca.upload.storage');
		static::$appStorage = static::$storage.static::$app->get('slug');
		
		static::$db         = \Mongo_Db::instance();
		static::$gfs        = \GridFs::getFs(static::$db);

		
		$this->get_summary();

		// if a Name was passed
		if ($filename)
		{
			$file =  static::$db
						->select( array(), array('_id'))
						->get_where(static::$dbCollectionName, array(
							'filename' => $filename
						), 1);

			if(count($file) == 1)
			{
				$this->file     = $file[0];
				$this->filename = $filename;
			}
			else
			{
				throw new LibraryException(
					__('tapioca.file_not_found', array('file' => $filename) )
				);	
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

	/**
	 * Return a summary of the library,
	 * include total files by categories
	 * and image resize presets
	 *
	 * @return  object
	 */
	public function get_summary()
	{
		$summary = static::$db
					->get_where(static::$dbCollectionName, array(
						'type' => 'summary'
					), 1);

		if (count($summary) == 1)
		{
			static::$summary = $summary[0];

			return true;
		}

		return false;
	}

	private function set_summary()
	{
		if( !static::$summary )
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
				->update(static::$dbCollectionName, $summary, array('upsert' => true));

			static::$summary = $this->get_summary();
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
					array('findandmodify' => static::$dbCollectionName,
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
		if( is_null( $this->filename ) )
		{
			throw new LibraryException(__('tapioca.no_file_selected'));
		}

		if( in_array( $preset_name, $this->file['presets'] ) )
		{
			return true;
		}

		$presets = static::$summary['presets'];

		if( !isset( $presets[$preset_name] ) )
		{
			throw new LibraryException(__('tapioca.preset_not_define'));
		}

		$original_file = $this->get_path();
		$path          = $this->get_path(false);
		$new_file_path = $path.$preset_name.'-'.$this->filename;
		$resource      = \Image::load($original_file);
		
		$resource->config('presets', $presets);
		$resource->preset($preset_name)->save($new_file_path);

		if( file_exists( $new_file_path ) )
		{
			$ret = static::$db
					->where(array(
						'filename' => $this->filename
					))
					->update(static::$dbCollectionName, array(
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

		$ret = new \stdClass;


		if( !is_null( $category ) )
		{
			$where['category'] = $category;

			if( $category == 'image' )
			{
				$ret->presets = static::$summary['presets'];
			}
		}
		else
		{
			$ret->categories           = new \stdClass;
			$ret->categories->image    = static::$summary['image'];
			$ret->categories->video    = static::$summary['video'];
			$ret->categories->document = static::$summary['document'];
		}

		if( !is_null( $tag ) )
		{
			$where['tags.key'] = $tag;
		}


		$hash = static::$db
					->where($where)
					->hash( static::$dbCollectionName, true );

		return array_merge( (array) $ret, (array) $hash );
	}

	public function read()
	{
		if( is_null( $this->file ) )
		{
			throw new LibraryException(__('tapioca.no_file_selected'));
		}

		return $this->file;
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
			//throw new LibraryException(__('tapioca.no_file_selected'));
		}
		
		$query = array( 'filename' => $this->filename,
						'appid'    => static::$app->get('id'));

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
	 * Catch files from upload and save them
	 *
	 * @param   object User instance
	 * @param   bool is Update 
	 * @return  bool
	 * @throws  LibraryException
	 */
	public function save(User $user, $update = false)
	{
		$files  = $this->upload();

		$result = $this->doIt($user, $files, $update);

		return $result;
	}

	/**
	 * Update file's metadata
	 *
	 * @param   object User instance
	 * @param   array files metadata
	 * @return  array
	 * @throws  LibraryException
	 */
	public function update(User $user, $fields)
	{
		// init update array
		$update = array();

		// if updating basename
		if( array_key_exists('basename', $fields) and
			$fields['basename'] != $this->file['basename'] and
			$this->filenameAvalaible( $fields['basename'].'.'.$this->file['extension'] ))
		{
			throw new \LibraryException(
				__( 'tapioca.file_already_exists', array('name' => $fields['basename(path)']) )
			);
		}
		elseif (array_key_exists('basename', $fields) and
				empty( $fields['basename'] ) )
		{
			throw new \LibraryException(__('tapioca.file_basename_empty'));
		}
		elseif (array_key_exists('basename', $fields))
		{
			$update['basename'] = $fields['basename'];
			$update['filename'] = $update['basename'].'.'.$this->file['extension'];

			unset($fields['basename']);

			// update files name on filesystem

			$file_path = static::$appStorage.DIRECTORY_SEPARATOR.$this->file['category'].DIRECTORY_SEPARATOR;

			$prefix    = array_merge( static::$presets, $this->file['presets'] );

			foreach( $prefix as $p)
			{
				if( !empty( $p))
					$p = $p.'-';

				$old = $file_path.$p.$this->file['filename'];
				$new = $file_path.$p.$update['filename'];

				File::rename( $old, $new );
			}
		}

		// if updating tags
		if( array_key_exists('tags', $fields) )
		{
			$update['tags']     = static::setTags( $fields['tags'] );			
		}


		if (empty($update))
		{
			return true;
		}

		// add update time
		$update['updated'] = new \MongoDate();
		$update['user']    = $user->get('id');

		$ret = static::$db
				->where(array('ref' => $this->file['ref']))
				->update(static::$dbCollectionName, $update);

		if( $ret )
		{
			// TODO: worker update dependencies

			return array_merge( $this->file, $update );
		}
		else
		{
			throw new \LibraryException( __('tapioca.internal_server_error') );	
		}
	}

	/**
	 * Import file from the filesystem to tapioca
	 *
	 * @param   object User instance
	 * @param   array files list
	 * @param   array tags
	 * @return  array
	 * @throws  LibraryException
	 */
	public function import(User $user, array $files, array $tags = array())
	{
		$fileTypes = \Config::get('tapioca.file_types');
		$finfo     = new \finfo(FILEINFO_MIME);
		$imported  = array();

		foreach($files as $file)
		{
			$minetype	= explode(';', $finfo->file($file['path']));
			$fileinfo	= pathinfo($file['path']);

			$filename   = static::setFileName($file['path']);
			// $filename	= \Inflector::friendly_title(trim(strtolower($fileinfo['filename'])));
			// $basename   = $filename.'.'.$fileinfo['extension'];

			$category   = static::getFileCategory($minetype[0]);

			$new_file = array(
				'saved_as'  => $fileinfo['basename'],
				'path'      => $file['path'],
				'mimetype'  => $minetype[0],
				'extension' => $fileinfo['extension'],
				'basename'  => $filename->filename,
				'filename'  => $filename->basename,
				'length'    => filesize($file['path']),
				'md5'       => md5_file($file['path']),
				'tags'      => $file['tags'],
				'category'  => $category,
			);

			// if file is an image, we get width/height
			if(strpos($minetype[0], 'image') !== false)
			{
				$new_file['size'] = array();
				list($new_file['size']['width'], $new_file['size']['height']) = getimagesize($file['path']);
			}

			$imported[] = $new_file;
		}

		return $this->doIt($user, $imported, false, true);
	}

	/**
	 * List of files to create/update
	 *
	 * @param   object User instance
	 * @param   bool is Update 
	 * @return  bool
	 * @throws  LibraryException
	 */
	private function doIt(User $user, array $files, $update = false, $import = false)
	{
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

					$ret         = $this->create($file, $user, $update, $import);
					$appslug     = static::$app->get('slug');
					$file_uri    = array('appslug' => $appslug, 'category' => $file['category'], 'filename' => $file['filename']);

					$api_url     = \Router::get('api_library_filename', $file_uri  );
					$file_url    = \Uri::create('files/:appslug/:category/:filename', $file_uri );

					$preview_url = (strpos($file['mimetype'], 'image') !== false) ?
							\Uri::create('files/:appslug/:category/preview-:filename', $file_uri ) : '';

					$result[] = array(
						'name'          => $file['filename'],
						'size'          => $file['length'],
						'url'           => $file_url,
						'thumbnail_url' => $preview_url,
						'delete_url'    => $api_url,
						'delete_type'   => 'DELETE'
					);
				}
				catch(LibraryException $e)
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

					if(!$import)
					{
						unlink($file['path']);
					}
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
	 * @throws  LibraryException
	 */
	public function create(array &$fields, User $user, $update = false, $import = false)
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

		try
		{
			$fields['uid'] = (string) static::$gfs
										->storeFile($file_path, array(
											'filename' => $fields['filename'],
											'appid'    => static::$app->get('id'),
											'category' => $fields['category']
										));
		}
		catch(\MongoGridFSException $e)
		{
			throw new \LibraryException(
				__('tapioca.fail_to_store_file', array('filename' => $fields['filename'], 'error' => $e->getMessage()))
			);
		}

		$new_file = array(
			'ref'     => uniqid(),
			'created' => new \MongoDate(),
			'presets' => $presets,
			'user'    => $user->get('id'),
		) + $fields;

		// do not work when upload multiple files
		$this->file      = $new_file;
		$this->filename  = $new_file['filename'];

		$ret = static::$db
				->where(array(
					'filename' => $new_file['filename']
				))
				->update(static::$dbCollectionName, $new_file, array('upsert' => true));

		// if first file upload, create collection's summary
		$this->set_summary();

		if($ret & !$update)
		{
			$this->inc_summary($new_file['category']);
		}

		// preview
		if($ret && strpos($fields['mimetype'], 'image') !== false)
		{
			$saved_to        = Config::get('tapioca.upload.path');
			$preview_tmpname = 'preview-'.$saved_as;
			$preview_name    = 'preview-'.$fields['filename'];
			$preview_path    = $saved_to.DIRECTORY_SEPARATOR.$preview_tmpname;

			\Image::load($file_path)
				->config('bgcolor', null)
				->config('quality', 60)
				->config('filetype', 'png')
				->crop_resize(100, 100)
				->save($preview_path);

			// $saved_to        = Config::get('tapioca.upload.path');
			// $preview_tmpname = 'preview-'.$saved_as;
			// $preview_name    = 'preview-'.$fields['filename'];
			// $preview_path    = $saved_to.DIRECTORY_SEPARATOR.$preview_tmpname;

			$this->store($preview_name, $preview_path, $fields['category'], false);	
		}

		$this->store($fields['filename'], $file_path, $fields['category'], $import);	
	}

	/**
	 * Check if a file as the same name OR if the same file exist (md5)
	 *
	 * @param   string file name
	 * @return  string file md5
	 * @return  bool
	 * @throws  LibraryException
	 */
	private function exists($filename, $md5)
	{
		// query db to check for filename
		$result = static::$db
						->or_where(array(
							'filename' => $filename,
							'md5'      => $md5
						))
						->get(static::$dbCollectionName);

		if (count($result) > 0)
		{
			foreach ($result as $file)
			{
				if($file['md5'] == $md5)
				{
					throw new LibraryException(
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

	/**
	 * Check if a filename is avalaible
	 *
	 * @param   string file name
	 * @return  bool
	 */
	private function filenameAvalaible($filename)
	{
		// query db to check for filename
		$result = static::$db
						->where(array(
							'filename' => $filename
						))
						->get(static::$dbCollectionName);

		if (count($result) > 0)
		{
			foreach ($result as $file)
			{
				if($file['filename'] == $filename)
				{
					return true;
				}
			}
		}

		return false;
	}

	private function store($filename, $path, $category, $import)
	{
		$cat_path     = static::$appStorage.DIRECTORY_SEPARATOR.$category;
		$file_path    = $cat_path.DIRECTORY_SEPARATOR.$filename;

		if(!is_dir(static::$appStorage))
		{
			File::create_dir(static::$storage, static::$app->get('slug'), 0755);			
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

		if(!$import)
		{
			unlink($path);
		}
	}

	public function delete_all(User $user)
	{
		$files = static::$db
					->get_where(static::$dbCollectionName, array(
						'type'   => array('$ne' => 'summary')
					));

		foreach($files as $file)
		{
			$this->file     = $file;
			$this->filename = $file['filename'];

			$this->delete();
		}

		exit;
	}

	public function delete($soft = false)
	{
		if( is_null( $this->filename ) )
		{
			throw new LibraryException(__('tapioca.no_file_selected'));
		}

		if( isset( $this->file['presets'] ) )
		{
			static::$presets = array_merge(static::$presets, $this->file['presets']); 
		}

		$fileGfs  = static::$gfs
						->findOne(array(
							'filename' => $this->filename,
							'appid'    => static::$app->get('id')
						));

		if(count($fileGfs) > 0)
		{
			//Get the GridFS Object and Remove file
			static::$gfs->delete($fileGfs->file['_id']);

			// delete files + mongoDb data
			if(!$soft)
			{
				$cat_path = static::$appStorage.DIRECTORY_SEPARATOR.$this->file['category'];

				if($this->file['category'] == 'image')
				{
					foreach(static::$presets as $preset)
					{
						$filename  = (empty($preset)) ? $this->filename : $preset.'-'.$this->filename;

						$file_path = $cat_path.DIRECTORY_SEPARATOR.$filename;

						File::delete($file_path);
					}
				}
				else
				{
					$file_path = $cat_path.DIRECTORY_SEPARATOR.$this->filename;
					File::delete($file_path);
				}

				$delete =  static::$db
							->where(array(
									'ref' => $this->file['ref']
							))
							->delete_all(static::$dbCollectionName);

				if($delete)
				{
					$this->inc_summary($this->file['category'], -1);
				}

				$this->file     = null;
				$this->filename = null;
			}
		}
	}

	public static function getFileCategory($mimetype)
	{
		$file_types = Config::get('tapioca.file_types');		
		$category   = 'other';

		foreach ($file_types as $key => $values)
		{
			if(in_array($mimetype, $values))
			{
				return $key;
			}
		}

		return $category;
	}

	public static function setTags( $tags )
	{
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

		return $file_tags;
	}

	public static function setFileName($path, $name = false)
	{
		$fileinfo	= pathinfo($path);
		$name       = (!$name) ? $fileinfo['filename'] : $name;

		$filename	= \Inflector::friendly_title(trim(strtolower($name)));
		$basename   = $filename.'.'.$fileinfo['extension'];

		return (object) array(
				'filename' => $filename,
				'basename' => $basename
			);
	}


	public function upload()
	{
		$config     = Config::get('tapioca.upload');

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

		$file_tags = static::setTags( $tags );

		// if there are any valid files
		if (Upload::is_valid())
		{
			// save them according to the config
			Upload::save();

			foreach( Upload::get_files() as $file )
			{
				$file_path  = $file['saved_to'].$file['saved_as'];

				// $basename	= \Inflector::friendly_title(trim(strtolower($file['filename'])));
				// $filename   = $basename.'.'.$file['extension'];
				$filename   = static::setFileName($file_path, $file['filename']);
				$category   = static::getFileCategory($file['mimetype']);

				$new_file = array(
								'saved_as'  => $file['saved_as'],
								'path'      => $file_path,
								'mimetype'  => $file['mimetype'],
								'extension' => $file['extension'],
								'basename'  => $filename->filename,
								'filename'  => $filename->basename,
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