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

class Document
{
    private function setValue( &$data, $path, $value )
    {
        $temp = &$data;

        foreach ( $path as $key )
        {
            $temp = &$temp[$key];
        }

        $temp = $value;

        return $value ;
    }

    public function perform()
    {
        // \Cli::write(print_r($this->args, true));

        Tapioca::base();

        $db = \Mongo_Db::instance();

        $dbCollectionName = $this->args['appslug'].'-'.$this->args['collection'];
        $where = array(
                        '_ref'              => $this->args['_ref'],
                        '_tapioca.revision' => $this->args['revision'],
                    );

        // list all collections with dependencies
        $collections = $db
                        ->select( array('namespace', 'dependencies') )
                        ->where(array(
                            'app_id'                  => $this->args['appslug'],
                            'active'                  => true,
                            'dependencies.collection' => $this->args['collection']
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

        foreach( $collections as $collection)
        {
            foreach( $collection['dependencies'] as $dependency )
            {
                if( $dependency['collection'] == $this->args['collection'] )
                {
                    $path             = $dependency['path'].'.ref';
                    $dbCollectionName = $this->args['appslug'].'-'.$collection['namespace'];

                    $set = array();

                    foreach( $dependency['fields'] as $field )
                    {
                        $set[ $field ] = \Arr::get( $originDoc, $field, null);
                    }

                    // $pathKeys = explode('.', $dependency['path']);

                    // if( count( $pathKeys ) > 1 )
                    // {
                    //     $pathArr  = array();
                    //     $pathVal  = array( 'embedded' => $set );

                    //     $this->setValue( $pathArr, $pathKeys, $pathVal );
                    //     $update = array('$set' => $pathArr );

                    //     \Cli::write(print_r($update, true));
                    //     exit;
                    // }
                    // else
                    // {
                        $update = array('$set' => array($dependency['path'].'.embedded' => $set) );
                    // }
                    // \Cli::write(print_r($update, true));
                    // exit;
                    $where = array( $path             => $this->args['_ref'],
                                    '_tapioca.locale' => $this->args['locale'],
                                    '_tapioca.status' => 100
                                );

                    // \Cli::write( $dbCollectionName );
                    // \Cli::write(print_r($where, true));
                    // \Cli::write(print_r($update, true));

                    try
                    {
                        $documents = $db
                                        ->where( $where )
                                        ->update_all( $dbCollectionName, $update, true );

                    }
                    catch( \Mongo_DbException $e )
                    {
                        \Cli::write( $e->getMessage() );
                    }

                }
            }

        }

    }
}