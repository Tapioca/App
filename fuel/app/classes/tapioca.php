<?php

namespace Tapioca;

use FuelException;
use Config;
use Lang;

class TapiocaException extends \FuelException {}

class Tapioca 
{
	/**
	 * @var  string  Database instance
	 */
	private static $db = null;

	/**
	 * Prevent instantiation
	 */
	final private function __construct() {}

	/**
	 * Run when class is loaded
	 *
	 * @return  void
	 */
	public static function _init()
	{
		// load config
		Config::load('tapioca', true);
		Lang::load('tapioca', 'tapioca');
	}

	/**
	 * @param   string app id
	 * @param   MongoId|string Collection id.
	 * @throws  TapiocaException
	 * @return  Collection
	 */
	public static function collection($appid, $id = null)
	{
		try
		{
			return new \Collection($appid, $id);
		}
		catch (TapiocaCollectionException $e)
		{
			throw new \TapiocaException($e->getMessage());
		}

		//\Debug::dump('Tapioca collection call');
		//
	}

	/**
	 * @param   string app slug
	 * @param   string collection namespace.
	 * @param   string document reference.
	 * @throws  TapiocaException
	 * @return  Document
	 */
	public static function document($app_slug, $namespace, $ref = null)
	{
		try
		{
			return new \Document($app_slug, $namespace, $ref);
		}
		catch (TapiocaDocumentException $e)
		{
			throw new \TapiocaException($e->getMessage());
		}

		//\Debug::dump('Tapioca collection call');
		//
	}

	public static function set_status($status = array())
	{
		$defaults = Config::get('tapioca.status');

		if(count($status) > 1)
		{
			return array_merge($defaults, $status);
		}

		return $defaults;
	}
}