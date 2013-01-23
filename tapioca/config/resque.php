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
 *
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your tapioca/config/ENV folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(
	'queue'         => 'TapiocApp',
	'hostname'      => '127.0.0.1',
	'port'          => 6379,
	'prefix'        => 'TapiocApp:',
	'interval'      => 5,
	'count'	    	=> 1,
);

// end of file resque.php
