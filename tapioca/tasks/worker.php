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

namespace Fuel\Tasks;

class Worker 
{
    public static function run()
    {
        $_SERVER['FUEL_ENV'] = \Cli::prompt('environment?', 'development'); // 'production');
        $interval            = \Cli::prompt('interval?', '5');
        $logLevel            = \Cli::prompt('log level?', '0');

        \Tapioca::base();

        // Start a single worker
        $worker = new \Tapioca\Worker();
        $worker->logLevel = $logLevel;
            
        $PIDFILE = getenv('PIDFILE');
        if ($PIDFILE) {
            file_put_contents($PIDFILE, getmypid()) or
                die('Could not write PID information to ' . $PIDFILE);
        }

        \Cli::write('*** Starting worker '.$worker."\n", 'green');
        
        $worker->work($interval);

    }

    public static function help()
        {
            echo <<<HELP
Usage:
    php oil refine worker

Fuel options:


Description:
    MongoDb based background jobs, placing those jobs on queue, and processing them later.

Examples:
    php oil r worker

HELP;

        }

 }