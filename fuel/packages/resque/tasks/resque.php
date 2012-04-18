<?php

namespace Fuel\Tasks;

class Resque 
{
 	public static function run()
 	{
		\Config::load('resque', true);

		$QUEUE               = \Cli::prompt('queue?', \Config::get('resque.queue'));
		$_SERVER['FUEL_ENV'] = \Cli::prompt('environment?', 'production');

		if (empty($QUEUE))
		{
			\Cli::error('Call me with : QUEUE="NomDeMaQueue" ENV="NomDeMonEnvironement" php workers.php');
		}

		$REDIS_BACKEND = \Config::get('resque.redis_backend');
		if(!empty($REDIS_BACKEND))
		{
			Resque::setBackend($REDIS_BACKEND);
		}

		$logLevel = 0;
		$LOGGING  = \Config::get('resque.logging');
		$VERBOSE  = \Config::get('resque.verbose');
		$VVERBOSE = \Config::get('resque.vverbose');

		if(!empty($LOGGING) || !empty($VERBOSE))
		{
			$logLevel = Resque_Worker::LOG_NORMAL;
		}
		else if(!empty($VVERBOSE))
		{
			$logLevel = Resque_Worker::LOG_VERBOSE;
		}

		$interval = \Config::get('resque.interval');
		$count    = \Config::get('resque.count');

		if($count > 1)
		{
			for($i = 0; $i < $count; ++$i)
			{
				$pid = pcntl_fork();

				if($pid == -1)
				{
					\Cli::error("Could not fork worker ".$i."\n");
				}
				// Child, start the worker
				else if(!$pid)
				{
					$queues = explode(',', $QUEUE);
					$worker = new \Resque_Worker($queues);
					$worker->logLevel = $logLevel;

					\Cli::write('*** Starting worker '.$worker."\n", 'green');

					$worker->work($interval);
					break;
				}
			}
		}
		// Start a single worker
		else
		{
			$queues = explode(',', $QUEUE);
			$worker = new \Resque_Worker($queues);
			$worker->logLevel = $logLevel;
			
			$PIDFILE = getenv('PIDFILE');
			if ($PIDFILE) {
				file_put_contents($PIDFILE, getmypid()) or
					die('Could not write PID information to ' . $PIDFILE);
			}

			\Cli::write('*** Starting worker '.$worker."\n", 'green');

			$worker->work($interval);
		}
	}

	public static function status($token)
	{
		$status = new \Resque_Job_Status($token);

		switch($status->get())
		{
			case 1: 
						$str   = 'waiting';
						$color = '';
						break;
			case 2: 
						$str   = 'running';
						$color = '';
						break;
			case 3: 
						$str   = 'failed';
						$color = 'red';
						break;
			case 4: 
						$str   = 'complete';
						$color = 'green';
						break;
			default:
						$str   = 'false';
						$color = 'red';
		}

		\Cli::write(__('resque.status.'.$str), $color); // Outputs the status
	}

 	public static function help()
 		{
 			echo <<<HELP
Usage:
    php oil refine resque
    php oil refine resque:queue
    php oil refine resque:status "token"

Fuel options:


Description:
	Resque is a Redis-backed library for creating background jobs, placing those jobs on multiple queues, and processing them later.

Examples:
    php oil r resque

HELP;

 		}

 }