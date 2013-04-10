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

namespace Tapioca\Jobs\Storage;

use Tapioca;
use File;

class Create
{
    public function perform()
    {
        Tapioca::base();

        $app     = Tapioca::app( array( 'slug' => $this->args['appslug'] ) );
        $storage = new \Tapioca\Storage( $app ); 

        // get file content
        $fileContent = File::read( $this->args['filePath'], true );
        
        // remove original file
        unlink( $this->args['filePath'] );

        try
        {
            return $storage->store( $this->args['filename'], $this->args['category'], $fileContent );
        }
        catch( \RuntimeException $e )
        {
            throw new \JobsException( $e->getMessage() );
        }
    }
}