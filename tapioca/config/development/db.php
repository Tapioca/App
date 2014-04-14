<?php
/**
 * The development database settings. These get merged with the global settings.
 */

return array(
	'default' => array(
		'connection'  => array(
			'dsn'        => 'mysql:host=localhost;dbname=fuel_dev',
			'username'   => 'root',
			'password'   => 'root',
		),
	),
    'mongo' => array(
        // This group is used when no instance name has been provided.
        'default' => array(
            'hostname' => 'localhost',
            'database' => 'tapiocapp_beta_v2',
        ),
    ),
);
