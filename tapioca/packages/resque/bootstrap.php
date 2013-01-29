<?php

Autoloader::add_core_namespace('Resque');

Autoloader::add_classes(array(
	'Resque\\Resque'           => __DIR__.'/classes/Resque.php',
	'Resque\\Resque_Worker'    => __DIR__.'/classes/Resque/Worker.php',
	'Resque\\Resque_Event'     => __DIR__.'/classes/Resque/Event.php',
	'Resque\\Resque_Exception' => __DIR__.'/classes/Resque/Exception.php',
	'Resque\\RedisCluster'     => __DIR__.'/classes/Resque/RedisCluster.php',
	'Resque\\Resque_Stat'      => __DIR__.'/classes/Resque/Stat.php',

	'Resque\\Resque_Failure_Interface' => __DIR__.'/classes/Resque/Failure/Interface.php',
	'Resque\\Resque_Failure_Redis'     => __DIR__.'/classes/Resque/Failure/Redis.php',
	'Resque\\Resque_Failure'           => __DIR__.'/classes/Resque/Failure.php',

	'Resque\\Resque_Job'	                => __DIR__.'/classes/Resque/Job.php',
	'Resque\\Resque_Job_Status'             => __DIR__.'/classes/Resque/Job/Status.php',
	'Resque\\Resque_Job_DontPerform'        => __DIR__.'/classes/Resque/Job/DontPerform.php',
	'Resque\\Resque_Job_DirtyExitException' => __DIR__.'/classes/Resque/Job/DirtyExitException.php',

));


/* End of file bootstrap.php */
