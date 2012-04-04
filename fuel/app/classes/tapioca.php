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
	 * 
	 * @param   Collection namespace or ref.
	 * @throws  TapiocaException
	 * @return  Tapioca_Collection
	 */
	public static function collection($id = null)
	{
		try
		{
			return new \Collection($id);
		}
		catch (TapiocaCollectionException $e)
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