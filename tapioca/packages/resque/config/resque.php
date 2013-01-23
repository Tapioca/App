<?php

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
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
	'redis_backend' => '',
	'logging'       => '',
	'verbose'       => '', 
	'vverbose'      => ''
);

// end of file resque.php
