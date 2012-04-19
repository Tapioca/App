<?php

// Load in the Autoloader
require COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php';
class_alias('Fuel\\Core\\Autoloader', 'Autoloader');

// Bootstrap the framework DO NOT edit this
require COREPATH.'bootstrap.php';


Autoloader::add_core_namespace('Tapioca');


Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
	'Debug'				=> APPPATH.'classes/core/debug.php',
	'Phpredis'			=> APPPATH.'classes/phpredis.php',


	'Tapioca\\Tapioca'                          => APPPATH.'classes/tapioca.php',
	'Tapioca\\TapiocaException'                 => APPPATH.'classes/tapioca.php',
	'Tapioca\\TapiocaCollectionException'       => APPPATH.'classes/tapioca/collection.php', 

	'Tapioca\\Collection'                       => APPPATH.'classes/tapioca/collection.php',
	'Tapioca\\Document'                         => APPPATH.'classes/tapioca/document.php',

	// Resque jobs
	'Tapioca\\Jobs\\PHP_Job' => APPPATH.'classes/tapioca/jobs/job.php',
));

// Register the autoloader
Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGE
 * Fuel::PRODUCTION
 */
Fuel::$env = (isset($_SERVER['FUEL_ENV']) ? $_SERVER['FUEL_ENV'] : Fuel::DEVELOPMENT);

// Initialize the framework with the config file.
Fuel::init('config.php');
