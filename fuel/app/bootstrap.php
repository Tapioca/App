<?php

// Load in the Autoloader
require COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php';
class_alias('Fuel\\Core\\Autoloader', 'Autoloader');

// Bootstrap the framework DO NOT edit this
require COREPATH.'bootstrap.php';


Autoloader::add_core_namespace('Tapioca');
Autoloader::add_core_namespace('Auth');


Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
	'Mongo_Db'			=> APPPATH.'classes/core/mongo/db.php',
	'GridFs'			=> APPPATH.'classes/core/mongo/gridfs.php',	

	'Debug'				=> APPPATH.'classes/core/debug.php',
	'Phpredis'			=> APPPATH.'classes/core/phpredis.php',
	'Set'			    => APPPATH.'classes/core/set.php',

	// Auth
	'Auth\\Auth'                    => APPPATH.'classes/auth.php',
	'Auth\\AuthException'           => APPPATH.'classes/auth.php',
	'Auth\\User'                    => APPPATH.'classes/auth/user.php',
	'Auth\\UserException'           => APPPATH.'classes/auth/user.php',
	'Auth\\UserNotFoundException'   => APPPATH.'classes/auth/user.php',
	'Auth\\Group'                   => APPPATH.'classes/auth/group.php',
	'Auth\\GroupException'          => APPPATH.'classes/auth/group.php',
	'Auth\\GroupNotFoundException'  => APPPATH.'classes/auth/group.php',
	'Auth\\Attempts'                => APPPATH.'classes/auth/attempts.php',
	'Auth\\AttemptsException'       => APPPATH.'classes/auth/attempts.php',
	'Auth\\UserSuspendedException'  => APPPATH.'classes/auth/attempts.php',

	// Tapioca
	'Tapioca\\Tapioca'                    => APPPATH.'classes/tapioca.php',
	'Tapioca\\TapiocaException'           => APPPATH.'classes/tapioca.php',
	'Tapioca\\TapiocaCollectionException' => APPPATH.'classes/tapioca/collection.php', 
	'Tapioca\\TapiocaDocumentException'   => APPPATH.'classes/tapioca/document.php',  
	'Tapioca\\TapiocaFileException'       => APPPATH.'classes/tapioca/file.php', 

	'Tapioca\\Collection'                 => APPPATH.'classes/tapioca/collection.php',
	'Tapioca\\Document'                   => APPPATH.'classes/tapioca/document.php',
	'Tapioca\\Rules'                      => APPPATH.'classes/tapioca/rules.php',
	'Tapioca\\File'                       => APPPATH.'classes/tapioca/file.php',

	// Tapioca Resque jobs
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
