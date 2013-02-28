<?php
/**
 * Tapioca: Schema Driven Data Engine 
 * Flexible CMS build on top of MongoDB, FuelPHP and Backbone.js
 *
 * @package   Tapioca
 * @version   v0.8
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/App
 */

namespace Tapioca;

use FuelException;
use Config;
use Upload;
use File;
use Gaufrette\Filesystem;

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
    protected static $rootStorage = null;

    /**
     * @var  string  app preview storage
     */
    protected static $appStorage = null;

    /**
     * @var  array  prefix for presets
     */
    protected static $presets = array('');

    /**
     * @var  array Errors list
     */
    protected $errors = array();

    /**
     * Loads in the File object
     *
     * @param   object  App instance
     * @param   object  Filesystem instance
     * @param   string  Filename
     * @return  void
     */
    public function __construct(App $app, $filename = null)
    {
        // load and set config
        static::$app              = $app;
        static::$dbCollectionName = static::$app->get('slug').'--library';

        static::$storage     = new Storage( static::$app ); 

        static::$rootStorage = Config::get('tapioca.upload.storage');
        static::$appStorage  = static::$rootStorage.static::$app->get('slug');
        
        static::$db          = \Mongo_Db::instance();
        static::$gfs         = \GridFs::getFs(static::$db);

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
     * apply preset to a file
     *
     * @param   string preset name
     * @return  bool
     */
    public function preset( $presetName )
    {
        if( is_null( $this->filename ) )
        {
            throw new LibraryException(__('tapioca.no_file_selected'));
        }

        if( $this->file['category'] != 'image' )
        {
            throw new LibraryException( __('tapioca.wrong_filetype_for_preset') );
        }

        if( in_array( $presetName, $this->file['presets'] ) )
        {
            return true;
        }

        $presets = static::$app->get('library.presets');

        if( !isset( $presets[ $presetName ] ) )
        {
            throw new LibraryException(__('tapioca.preset_not_define'));
        }

        $resqueArgs = array(
            'appslug'    => static::$app->get('slug'),
            'filename'   => $this->filename,
            'presetName' => $presetName
        );
        
        // delegate to worker
        Tapioca::enqueueJob( 'bdn', '\\Tapioca\\Jobs\\Library\\Preset', $resqueArgs, null);

        return true;

        // $original_file = $this->get_path();
        // $path          = $this->get_path(false);
        // $new_file_path = $path.$preset_name.'-'.$this->filename;
        // $resource      = \Image::load($original_file);
        
        // $resource->config('presets', $presets);
        // $resource->preset($preset_name)->save($new_file_path);

        // if( file_exists( $new_file_path ) )
        // {
        //     $ret = static::$db
        //             ->where(array(
        //                 'filename' => $this->filename
        //             ))
        //             ->update(static::$dbCollectionName, array(
        //                 '$addToSet' => array(
        //                     'presets' => $preset_name
        //                 )
        //             ), array(), true);

        //     return $ret;
        // }
    }

    public function getAll($category = null, $tag = null)
    {
        $where = array();

        if( !is_null( $category ) )
        {
            $where['category'] = $category;
        }

        if( !is_null( $tag ) )
        {
            $where['tags.key'] = $tag;
        }

        $hash = static::$db
                    ->where($where)
                    ->hash( static::$dbCollectionName, true );

        return $hash;
    }

    public function get()
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
            $this->get();
            //throw new LibraryException(__('tapioca.no_file_selected'));
        }
        
        $query = array( '_id'   => new \MongoId( $this->file['uid'] ),
                        'appid' => static::$app->get('id') );

        $query['preview'] = ($preview) ? true : array( '$exists' => false );

        return static::$gfs->findOne( $query );

        // $result = array();

        // foreach($cursor as $c)
        // {
        //     $result[] = $c;
        // }
        // return $result;
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

            // update filenames

            $prefix    = array_merge( static::$presets, $this->file['presets'] );

            foreach( $prefix as $p)
            {
                if( !empty( $p ))
                    $p = $p.'-';

                // $old = $p.$this->file['filename'];
                // $new = $p.$update['filename'];

                // static::$storage->rename( $this->file['category'], $old, $new );

                $resqueArgs = array(
                    'appslug'  => static::$app->get('slug'),
                    'old'      => $p.$this->file['filename'],
                    'new'      => $p.$update['filename'],
                    'category' => $this->file['category']
                );
                
                // check for documents to update
                Tapioca::enqueueJob( static::$app->get('slug'), '\\Tapioca\\Jobs\\Storage\\Rename', $resqueArgs, null);
            }

            // update preview filename

            $previewStorage = new Storage( static::$app, true ); 
            $previewStorage->rename( $this->file['category'], 'preview-'.$this->file['filename'], 'preview-'.$update['filename'] );

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
                ->where(array('_ref' => $this->file['_ref']))
                ->update(static::$dbCollectionName, $update);

        if( $ret )
        {
            $resqueArgs = array(
                'appslug'    => static::$app->get('slug'),
                '_ref'       => $this->file['_ref']
            );
            
            // check for documents to update
            Tapioca::enqueueJob( static::$app->get('slug'), '\\Tapioca\\Jobs\\Dependency\\File', $resqueArgs, null);

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
            $minetype   = explode(';', $finfo->file($file['path']));
            $fileinfo   = pathinfo($file['path']);

            $filename   = static::setFileName($file['path']);
            // $filename    = \Inflector::friendly_title(trim(strtolower($fileinfo['filename'])));
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

                    $tmp = array(
                        'name'          => $file['filename'],
                        'size'          => $file['length'],
                        'category'      => $file['category'],
                        'url'           => $file_url,
                        'thumbnail_url' => $preview_url,
                        'delete_url'    => $api_url,
                        'delete_type'   => 'DELETE'
                    );

                    if( isset( $file['size'] ))
                    {
                        $tmp['isize'] = $file['size'];
                    }

                    $result[] = $tmp;
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
        $filePath = $fields['path'];
        $saved_as  = $fields['saved_as'];

        unset($fields['path']);
        unset($fields['saved_as']);

        $presets   = array();

        if(!is_null($this->filename) && $update)
        {
            // get previous file's data
            $previous = $this->get();

            // remove mongoID
            unset($previous['_id']);

            $fields['basename']  = $previous['basename'];
            $fields['filename']  = $previous['basename'].'.'.$fields['extension'];

            $presets = $previous['presets'];

            $this->delete();
        }

        try
        {
            $fields['uid'] = (string) static::$gfs
                                        ->storeFile($filePath, array(
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
            '_ref'    => uniqid(),
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

        if($ret & !$update)
        {
            static::$app->inc_library($new_file['category']);
        }

        // preview
        if($ret && strpos($fields['mimetype'], 'image') !== false)
        {
            $saved_to        = Config::get('tapioca.upload.path');
            $preview_tmpname = 'preview-'.$saved_as;
            $preview_name    = 'preview-'.$fields['filename'];
            $preview_path    = $saved_to.DIRECTORY_SEPARATOR.$preview_tmpname;

            \Image::load($filePath)
                ->config('bgcolor', null)
                ->config('quality', 60)
                ->config('filetype', 'png')
                ->crop_resize(100, 100)
                ->save($preview_path);

            // get file content
            $fileContent = File::read( $preview_path, true );

            // remove preview file
            unlink($preview_path);

            $previewStorage = new Storage( static::$app, true ); 

            $previewStorage->store($preview_name, $fields['category'], $fileContent ); 
        }

        // // get file content
        // $fileContent = File::read( $filePath, true );
        
        // // remove original file
        // unlink($filePath);

        // static::$storage->store( $fields['filename'], $fields['category'], $fileContent );

        $resqueArgs = array(
            'appslug'  => static::$app->get('slug'),
            'filePath' => $filePath,
            'filename' => $fields['filename'], 
            'category' => $fields['category']
        );
        
        // check for documents to update
        Tapioca::enqueueJob( static::$app->get('slug'), '\\Tapioca\\Jobs\\Storage\\Create', $resqueArgs, null);
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

    public function delete_all(User $user)
    {
        $files = static::$db->get(static::$dbCollectionName);

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
                            '_id'   => new \MongoId( $this->file['uid'] ),
                            'appid' => static::$app->get('id')
                        ));

        if(count($fileGfs) > 0)
        {
            //Get the GridFS Object and Remove file
            static::$gfs->delete($fileGfs->file['_id']);

            // delete files + mongoDb data
            if( !$soft )
            {
                if( $this->file['category'] == 'image' )
                {
                    foreach( static::$presets as $preset )
                    {
                        $filename  = (empty($preset)) ? $this->filename : $preset.'-'.$this->filename;

                        $resqueArgs = array(
                            'appslug'  => static::$app->get('slug'),
                            'filename' => $filename,
                            'category' => $this->file['category']
                        );
                        
                        // check for documents to update
                        Tapioca::enqueueJob( static::$app->get('slug'), '\\Tapioca\\Jobs\\Storage\\Delete', $resqueArgs, null);
                    }

                    $previewStorage = new Storage( static::$app, true ); 

                    $previewStorage->delete( $this->file['category'], 'preview-'.$this->filename ); 
                }
                else
                {
                    $resqueArgs = array(
                        'appslug'  => static::$app->get('slug'),
                        'filename' => $this->filename,
                        'category' => $this->file['category']
                    );
                    
                    // check for documents to update
                    Tapioca::enqueueJob( static::$app->get('slug'), '\\Tapioca\\Jobs\\Storage\\Delete', $resqueArgs, null);
                }

                $delete =  static::$db
                            ->where(array(
                                    '_ref' => $this->file['_ref']
                            ))
                            ->delete_all(static::$dbCollectionName);

                if($delete)
                {
                    static::$app->inc_library($this->file['category'], -1);
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
        $fileinfo   = pathinfo($path);
        $name       = (!$name) ? $fileinfo['filename'] : $name;

        $filename   = \Inflector::friendly_title(trim(strtolower($name)));
        $basename   = $filename.'.'.$fileinfo['extension'];

        return (object) array(
                'filename' => $filename,
                'basename' => $basename
            );
    }


    public function upload()
    {
        $config     = Config::get('tapioca.upload');

        // extends extension whitelist with app settings
        $config['ext_whitelist'] = static::$app->get('library.extwhitelist');

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

                // $basename    = \Inflector::friendly_title(trim(strtolower($file['filename'])));
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