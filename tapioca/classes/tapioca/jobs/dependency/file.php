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

namespace Tapioca\Jobs\Dependency;

use Tapioca;

class File
{
    public function perform()
    {
        // \Cli::write(print_r($this->args, true));

        Tapioca::base();

        $db = \Mongo_Db::instance();

        $dbCollectionName = $this->args['appslug'].'--library';
        $where = array( '_ref' => $this->args['_ref'] );

        // list all collections with dependencies
        $collections = $db
                        ->select( array('namespace', 'dependencies') )
                        ->where(array(
                            'app_id'                  => $this->args['appslug'],
                            'active'                  => true,
                            'dependencies.collection' => $dbCollectionName
                        ))
                        ->get( \Config::get('tapioca.collections.collections') );

        // \Cli::write(print_r($collections, true));

        if( count( $collections ) == 0)
        {
            return;
        }

        // source document
        $originDoc   = $db
                        ->where( $where )
                        ->get( $dbCollectionName );

        // \Cli::write($dbCollectionName);
        // \Cli::write(print_r($where, true));      
        // \Cli::write(print_r($originDoc, true));
        // \Cli::write( print_r($originDoc, true) );

        if( count( $originDoc ) != 1 )
        {
            return;
        }

        $originDoc = $originDoc[0];
        $set       = array(
                '_ref'     => $originDoc['_ref'], 
                'filename' => $originDoc['filename'], 
                'category' => $originDoc['category']
            );

        if( $originDoc['category'] == 'image' )
        {
            $set['size'] = $originDoc['size'];
        }

        foreach( $collections as $collection)
        {
            foreach( $collection['dependencies'] as $dependency )
            {
                if( $dependency['collection'] == $dbCollectionName )
                {
                    $path           = $dependency['path'].'._ref';
                    $collectionName = $this->args['appslug'].'-'.$collection['namespace'];

                    $update = array('$set' => array($dependency['path'] => $set) );

                    $where = array( $path             => $this->args['_ref'],
                                    // '_tapioca.locale' => $this->args['locale'],
                                    // '_tapioca.status' => 100
                                );

                    // \Cli::write( $dbCollectionName );
                    // \Cli::write(print_r($where, true));
                    // \Cli::write(print_r($update, true));

                    $documents = $db
                                    ->where( $where )
                                    ->update_all( $collectionName, $update, true );

                }
            }

        }

    }
}