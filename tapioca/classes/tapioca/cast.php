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
use Set;

class CastException extends FuelException {}

class Cast
{
    public static function set($fields, &$document)
    {
        foreach($fields as $field)
        {
            $results = Set::extract($field['path'], $document);
            $results = call_user_func_array(array('self', '_'.$field['type']), array($results));

            if( count($results) > 0)
            {
                self::apply($document, $field['path'], $results);
            }

        }

        return $document;
    }

    private static function apply(&$document, $path, $result)
    {
        $doc =& $document;
        $items  = array_filter(explode('/', $path));
        $target = end($items);

        foreach ($items as $key)
        {
            if($key == $target && !is_null( $doc ))
            {
                // our key is part of this level
                if( array_key_exists($target, $doc) )
                {
                    // if the key contains array
                    // result remplace all the values
                    if( is_array($doc[$key]) )
                    {
                        $doc[$key] = $result;
                    }
                    else
                    {
                        $doc[$key] = reset($result);
                    }
                }
                // our key is part of an array of object
                else
                {
                    $nbResult = count($result);

                    for($i = -1; ++$i < $nbResult;)
                    {
                        $doc[$i][$key] = $result[$i];
                    }
                }
            }
            else
            {
                $doc =& $doc[$key];
            }
        }

    }

    private static function _date($results)
    {
        return static::_number($results);
    }

    private static function _number($results)
    {
        array_walk($results, function(&$item)
        {
            $item = (int) $item;
        });

        return $results;
    }

    private static function _bool($results)
    {
        return static::_number($results);
    }
    
    // private static function makeMulti($path, $result)
    // {
    //  $multi  = array();
    //  $temp   =& $multi;
    //  $items  = array_filter(explode('/', $path));
    //  $target = end($items);
        
    //  foreach ($items as $key)
    //  {
    //      if($key == $target)
    //      {
    //          if(is_array($result))
    //          {
    //              foreach($result as $value)
    //              {
    //                  $temp[][$key] = $value;
    //              }
    //          }
    //          else
    //          {
    //              $temp[$key] = $value;
    //          }
    //      }
    //      else
    //      {
    //          $temp[$key] = array();
    //          $temp =& $temp[$key];
    //      }
    //  }

    //  return $multi;
    // } 
}