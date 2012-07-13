<?php

namespace Tapioca;

use FuelException;
use Set;

class TapiocaCastException extends FuelException {}

class Cast
{
	private static $types;
	private static $paths = array();

	public static function _init()
	{
		static::$types = \Config::get('tapioca.cast');
	}

	public static function set(&$document, $schema)
	{
		static::parse($schema);

		foreach(static::$paths as $path => $type)
		{
			$results = Set::extract($path, $document);
			$results = call_user_func_array(array('self', '_'.$type), array($results));

			//$casted  = self::makeMulti($path, $results);

			self::apply($document, $path, $results);//array_merge($document, $casted);

		}

		return $document;
	}

	private static function parse($schema, $path = '/')
	{
		foreach($schema as $item)
		{
			if($item['type'] == 'object' || $item['type'] == 'array')
			{
				$tmp_path = $path.$item['id'].'/';
				static::parse($item['node'], $tmp_path);
			}
			else
			{
				if(in_array($item['type'], static::$types))
				{
					$tmp_path = $path.$item['id'];

					static::$paths[$tmp_path] = $item['type'];
				}
			}
		}
	}

	private static function apply(&$document, $path, $result)
	{
		$doc =& $document;
		$items  = array_filter(explode('/', $path));
		$target = end($items);

		foreach ($items as $key)
		{
			if($key == $target)
			{
				if(is_array($result))
				{
					$nbResult = count($result);

					for($i = -1; ++$i < $nbResult;)
					{
						$doc[$i][$key] = $result[$i];
					}
				}
				else
				{
					$doc[$key] = $result;
				}
			}
			else
			{
				$doc =& $doc[$key];
			}
		}

	}

	private static function makeMulti($path, $result)
	{
		$multi  = array();
		$temp   =& $multi;
		$items  = array_filter(explode('/', $path));
		$target = end($items);
		
		foreach ($items as $key)
		{
			if($key == $target)
			{
				if(is_array($result))
				{
					foreach($result as $value)
					{
						$temp[][$key] = $value;
					}
				}
				else
				{
					$temp[$key] = $value;
				}
			}
			else
			{
				$temp[$key] = array();
				$temp =& $temp[$key];
			}
		}

		return $multi;
	} 

	private static function _date($results)
	{
		array_walk($results, function(&$item)
		{
			$item = (int) $item;
		});

		return $results;
	}

}