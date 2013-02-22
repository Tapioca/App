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
    public static function setAdaptator( App $app )
    {
        try
        {
            $storageMethod = $app->get('storage');            
        }
        catch( AppException $e )
        {
            $storageMethod = 'locale';
        }


        switch( $storageMethod )
        {
            case 'ftp':
                        $adapter    = new FtpAdapter('/test', 'unik.ultranoir.com', array('username' => 'unik', 'password' => 'n0iru1tr@', 'create' => true));
                        break;
            default:
                        $path       = Config::get('tapioca.upload.storage');
                        $appStorage = $path.$app->get('slug');

                        $adapter = new LocalAdapter( $appStorage, true );
        }

        return new Filesystem($adapter);
    }
}
