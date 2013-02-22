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
    'Controller_Rest'   => APPPATH.'classes/core/controller/rest.php',
    'Form_Instance'     => APPPATH.'classes/core/form/instance.php',

    'Mongo_Db'               => APPPATH.'classes/core/mongo/db.php',
    'GridFs'                 => APPPATH.'classes/core/mongo/gridfs.php', 
    'GridFsException'        => APPPATH.'classes/core/mongo/gridfs.php', 
    'MongoDbExport'          => APPPATH.'classes/core/mongo/export.php', 
    'MongoDbExportException' => APPPATH.'classes/core/mongo/export.php', 

    'Phpredis'          => APPPATH.'classes/core/phpredis.php',
    'Set'               => APPPATH.'classes/core/set.php',

    // Tapioca
    'Tapioca\\Tapioca'                    => APPPATH.'classes/tapioca.php',

    'Tapioca\\TapiocaException'           => APPPATH.'classes/tapioca.php',
    'Tapioca\\AuthException'              => APPPATH.'classes/tapioca.php',
    'Tapioca\\UserException'              => APPPATH.'classes/tapioca/user.php',
    'Tapioca\\UserNotFoundException'      => APPPATH.'classes/tapioca/user.php',
    'Tapioca\\AttemptsException'          => APPPATH.'classes/tapioca/attempts.php',
    'Tapioca\\UserSuspendedException'     => APPPATH.'classes/tapioca/attempts.php',
    'Tapioca\\AppException'               => APPPATH.'classes/tapioca/group.php',
    'Tapioca\\AppNotFoundException'       => APPPATH.'classes/tapioca/group.php',
    'Tapioca\\PermissionsException'       => APPPATH.'classes/tapioca/permissions.php',
    'Tapioca\\PreviewException'           => APPPATH.'classes/tapioca/preview.php',
    'Tapioca\\JobsException'              => APPPATH.'classes/tapioca/jobs.php',

    'Tapioca\\CollectionException'        => APPPATH.'classes/tapioca/collection.php', 
    'Tapioca\\DocumentException'          => APPPATH.'classes/tapioca/document.php',  
    'Tapioca\\LibraryException'           => APPPATH.'classes/tapioca/library.php', 
    'Tapioca\\HookException'              => APPPATH.'classes/tapioca/hook.php', 
    'Tapioca\\CastException'              => APPPATH.'classes/tapioca/cast.php', 
    'Tapioca\\InstallException'           => APPPATH.'classes/tapioca/install.php', 

    'Tapioca\\User'                       => APPPATH.'classes/tapioca/user.php',
    'Tapioca\\Attempts'                   => APPPATH.'classes/tapioca/attempts.php',
    'Tapioca\\Permissions'                => APPPATH.'classes/tapioca/permissions.php',
    'Tapioca\\App'                        => APPPATH.'classes/tapioca/app.php',
    'Tapioca\\Collection'                 => APPPATH.'classes/tapioca/collection.php',
    'Tapioca\\Document'                   => APPPATH.'classes/tapioca/document.php',
    'Tapioca\\Rules'                      => APPPATH.'classes/tapioca/rules.php',
    'Tapioca\\Library'                    => APPPATH.'classes/tapioca/library.php',
    'Tapioca\\Storage'                    => APPPATH.'classes/tapioca/storage.php',
    'Tapioca\\Hook'                       => APPPATH.'classes/tapioca/hook.php',
    'Tapioca\\Cast'                       => APPPATH.'classes/tapioca/cast.php',
    'Tapioca\\Preview'                    => APPPATH.'classes/tapioca/preview.php',
    'Tapioca\\Install'                    => APPPATH.'classes/tapioca/install.php',

    // Tapioca Resque jobs
    'Tapioca\\Jobs'                       => APPPATH.'classes/tapioca/jobs.php',
    'Tapioca\\Jobs\\PHP_Job'              => APPPATH.'classes/tapioca/jobs/job.php',
    'Tapioca\\Jobs\\Dependency'           => APPPATH.'classes/tapioca/jobs/dependency.php',
));

// Register the autoloader
Autoloader::register();

// Composer
require APPPATH.'vendor/autoload.php';

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
