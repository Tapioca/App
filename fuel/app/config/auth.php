<?php

return array(

	/*
	 * Table Names
	 */
	'collection' => array(
		'users'           => 'users',
		'groups'          => 'groups',
		'users_suspended' => 'users_suspended',
	),

	/*
	 * Session keys
	 */
	'session' => array(
		'user'     => 'tapioca_user',
		'provider' => 'tapioca_provider',
	),

	/*
	 * Remember Me settings
	 */
	'remember_me' => array(

		/**
		 * Cookie name credentials are stored in
		 */
		'cookie_name' => 'tapioca_rm',

		/**
		 * How long the cookie should last. (seconds)
		 */
		'expire' => 1209600, // 2 weeks
	),

	/**
	 * Limit Number of Failed Attempts
	 * Suspends a login/ip combo after a # of failed attempts for a set amount of time
	 */
	'limit' => array(

		/**
		 * enable limit - true/false
		 */
		'enabled' => true,

		/**
		 * number of attempts before suspensions
		 */
		'attempts' => 3,

		/**
		 * suspension length - minutes
		 */
		'time' => 3,
	),

);
