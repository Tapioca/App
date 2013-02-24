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


/**
 * Tapioca's Storage work with KnpLabs Gaufrette,
 * a PHP5 library that provides a filesystem abstraction layer
 *
 * https://github.com/KnpLabs/Gaufrette
 */

namespace Tapioca;

use Config;
use FuelException;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Adapter\Ftp as FtpAdapter;

class StorageException extends FuelException {}

class Storage
{
    // filesystem instances
    private $fs = array();

    private $adapter;

    private $app;

    public function __construct( App $app, $forceLocale = false )
    {
        $this->app = $app;

        if( $forceLocale )
        {
            $this->method = 'locale';
            return;
        }

        try
        {
            $this->method = $app->get('storage.method');
        }
        catch( AppException $e )
        {
            $this->method = 'locale';
        }
    }

    private function getApadtapor( $category )
    {
        if( array_key_exists( $category,  $this->fs ) )
        {
            return $this->fs[ $category ];
        }

        switch( $this->method )
        {
            case 'ftp':
                        $path     = $this->app->get('storage.path');
                        $host     = $this->app->get('storage.host');
                        $username = $this->app->get('storage.username');
                        $password = $this->app->get('storage.password');

                        if( substr( $path, -1) != '/' )
                            $path .=  '/';

                        $adapter    = new FtpAdapter( $path.$category, $host, array('username' => $username, 'password' => $password, 'create' => true));
                        break;
            default:
                        $path       = Config::get('tapioca.upload.storage');
                        $appStorage = $path.$this->app->get('slug');

                        $adapter = new LocalAdapter( $appStorage.DIRECTORY_SEPARATOR.$category, true );
        }

        $this->fs[ $category ] = new Filesystem( $adapter );

        return $this->fs[ $category ];
    }

    public function store( $filename, $category, $fileContent )
    {
        $fs = $this->getApadtapor( $category );

        if( $fs->has( $filename ) )
        {
            $this->delete( $category, $filename );
        }

        return $fs->write( $filename, $fileContent );
    }

    public function rename( $category, $sourceKey, $targetKey )
    {
        $fs = $this->getApadtapor( $category );

        return $fs->rename( $sourceKey, $targetKey );
    }

    public function delete( $category, $filename )
    {
        $fs = $this->getApadtapor( $category );

        return $fs->delete( $filename );
    }
}
