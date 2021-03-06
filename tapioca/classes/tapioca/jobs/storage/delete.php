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

class Delete
{
    public function perform()
    {
        Tapioca::base();

        $app     = Tapioca::app( array( 'slug' => $this->args['appslug'] ) );
        $storage = new \Tapioca\Storage( $app );

        try
        {
            return $storage->delete( $this->args['category'], $this->args['filename'] ); 
        }
        catch( \RuntimeException $e )
        {
            throw new \JobsException( $e->getMessage() );
        }
    }
}