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

        // set permission
        try
        {
            Permissions::set( static::$user, static::$app );
        }
        catch( PermissionsException $e )
        {
            static::error($e->getMessage());
            return;
        }

        // filename
        $filename  = $this->param('filename', null);
        $extension = Input::extension();
        // $storage   = Storage::setAdaptator( static::$app );

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
        try
        {
            Permissions::isGranted( 'list_files' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 401 );
            return;
        }

        if( !static::$filename )
        {
            // filters
            $category = Input::get('category', null);
            $tag      = Input::get('tag', null);

            $ret  = static::$file->getAll( $category, $tag );               
        }
        else
        {
            $ret  = static::$file->get();
        }

        static::$data   = $ret;
        static::$status = 200;
    }

    // add a file to the library
    // OR update file
    public function post_index()
    {
        try
        {
            Permissions::isGranted( 'upload_files' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 401 );
            return;
        }

        $update = (bool) static::$filename;
        
        static::$data   = static::$file->save( static::$user, $update );
        static::$status = 200;
    }

    // update a file meta
    public function put_index()
    {
        try
        {
            Permissions::isGranted( 'edit_files' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 401 );
            return;
        }

        $meta = Input::json();

        static::$data   = static::$file->update( static::$user, $meta );
        static::$status = 200;
    }

    public function delete_index()
    {
        try
        {
            Permissions::isGranted( 'delete_files' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 401 );
            return;
        }

        if( ! static::deleteToken( 'library', static::$filename ))
        {
            return;
        }

        $list = static::$file->delete();

        static::$data   = array('status' => 'ok');
        static::$status = 200;
    }

    public function post_teststorage()
    {
        $storage  = Input::json('storage', null);

        // get file content
        $fileContent = File::read( Config::get('tapioca.upload.storage_test'), true );
        $filename    = 'tapioca-test.png';

        switch( $storage['method'] )
        {
            case 'ftp':
                        $path     = $storage['path'];
                        $host     = $storage['host'];
                        $username = $storage['username'];
                        $password = $storage['password'];

                        if( substr( $path, -1) != '/' )
                            $path .=  '/';

                        $path .= 'image';

                        $adapter    = new Gaufrette\Adapter\Ftp( $path, $host, array('username' => $username, 'password' => $password, 'create' => true));
                        break;
        }

        try
        {
            $fs   = new Gaufrette\Filesystem( $adapter );
            $test = $fs->write( $filename, $fileContent );

            if( $test )
            {
                $fs->delete( $filename );

                static::$data   = array('status' => 'ok');
                static::$status = 200;
            }
        }
        catch( RuntimeException $e)
        {
            static::error( $e->getMessage(), 401);
        }
    }
}