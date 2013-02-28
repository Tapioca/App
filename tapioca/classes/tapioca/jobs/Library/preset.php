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

namespace Tapioca\Jobs\Library;

use Tapioca;
use File;
use Config;
use Image;
use Mongo_Db;

class Preset
{
    /**
     * apply preset to a file
     *
     * @param   string preset name
     * @return  bool
     */
    public function perform()
    {
        // \Cli::write('call perform'."\n", 'green');
        // \Cli::write(print_r($this->args, true));

        Tapioca::base();

        $db           = Mongo_Db::instance();
        $dbCollection = $this->args['appslug'].'--library';

        $app   = Tapioca::app( array( 'slug' => $this->args['appslug'] ) );
        $file  = Tapioca::library( $app, $this->args['filename'] );
        $bytes = $file->getBytes();

        $uploadPath  = Config::get('tapioca.upload.path');
        $tmpFilename = $this->args['appslug'].'-'.$file->file['_ref'].'.'.$file->file['extension'];
        $tmpFilePath = $uploadPath.'/'.$tmpFilename;

        File::create( $uploadPath, $tmpFilename, $bytes->getBytes() );

        $newFilename = $this->args['presetName'].'-'.$file->file['filename'];
        $newFilePath = $uploadPath.'/'.$newFilename;

        $presets  = $app->get('library.presets');
        $resource = Image::load( $tmpFilePath );
        
        $resource->config('presets', $presets);
        $resource->preset( $this->args['presetName'] )->save( $newFilePath );

        if( file_exists( $newFilePath ) )
        {
            $storage = new \Tapioca\Storage( $app ); 

            // get file content
            $fileContent = File::read( $newFilePath, true );

            // remove generated files
            unlink( $newFilePath );
            unlink( $tmpFilePath );

            $ret = $storage->store( $newFilename, $file->file['category'], $fileContent );

            if( $ret )
            {
                $ret = $db
                        ->where(array(
                            'filename' => $this->args['filename']
                        ))
                        ->update( $dbCollection, array(
                            '$addToSet' => array(
                                'presets' => $this->args['presetName']
                            )
                        ), array(), true);
                
                return $ret;
            }

            return false;
        }

        return false;
    }
}