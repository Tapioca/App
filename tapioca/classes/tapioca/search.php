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

use Config;
use FuelException;

class SearchException extends FuelException {}

class Search
{
    private static $_replace = array(
            "/[^A-Za-z0-9 ]/",
            "/\s+/",
        );

    private static $_by = array(
            ' ',
            ' '
        );

    private static function clean( $str )
    {
        return strtolower( preg_replace( self::$_replace, self::$_by, \Inflector::ascii( $str, true ) ) );
    }

    public static function index( $appslug, $namespace, $_ref, $digest )
    {
        $arr = array(
            '_ref'      => $_ref,
            'appslug'   => $appslug,
        );
        $where = array(
            'appslug'   => $appslug,
            '_ref'      => $_ref,
        );

        // document
        if( $namespace )
        {
            $arr['namespace']   = $namespace;
            $where['namespace'] = $namespace;

            $docTitle = '';
            $docBody  = array();
            $i        = 0;

            foreach( $digest as $str )
            {
                if( $i == 0 )
                {
                    // document title to display 
                    $arr['title']   = $str;

                    $str            = self::clean( $str );

                    // string use to refine score
                    $arr['booster'] = $str;

                    // concat all digest's string
                    $docBody[]      = $str;

                    $i = 1;
                }
                else
                {
                    $docBody[] = self::clean( $str );
                }
            }

            $arr['body']    = join(' ', $docBody);
        }
        // file
        else
        {
            $where['library'] = true;

            $arr['library']  = true;
            $arr['title']    = $digest['filename'];
            $arr['category'] = $digest['category'];
            $arr['booster']  = self::clean( $digest['filename'] );

            $docBody  = join(' ', array(
                    $digest['filename'],
                    $digest['tags'],
                    $digest['category'],
                ));

            $arr['body']    = self::clean( $docBody);
        }

        $dbCollectionName = $appslug.'--search';

        return Tapioca::db()
                    ->where( $where )
                    ->update( $dbCollectionName, $arr, array('upsert' => true) );
    }

    public static function get( $appslug )
    {
        $dbCollectionName = $appslug.'--search';

        return Tapioca::db()
                    ->select( array(), array('_id', 'appslug'))
                    ->where(array(
                        'appslug'   => $appslug
                    ))
                    ->get( $dbCollectionName );
    }

    public static function delete( $appslug, $namespace, $_ref )
    {
        $dbCollectionName = $appslug.'--search';
        $where = array(
            'appslug'   => $appslug,
            '_ref'      => $_ref,
        );

        if( $namespace )
            $where['namespace'] = $namespace;
        else
            $where['library'] = true;

        $ret = Tapioca::db()
                    ->where( $where )
                    ->delete( $dbCollectionName );
    }
}