<?php
return array(
	'_root_'     => 'welcome/index',   // The default route
	'_404_'      => 'welcome/404', // The main 404 route

	// FRONT	
	'app/(:any)' => 'welcome/index',
	'app'        => 'welcome/index',

	// API REST 

		// void
	'api/void'                                         => array('api/void',                    'name' => 'api_void'),

		// log
	'api/log/out'                                      => array('api/log/out',                 'name' => 'api_log_out'),
	'api/log'                                          => array('api/log',                     'name' => 'api_log'),

		// user
	'api/user/me'                                      => array('api/user/me',                 'name' => 'api_user_me'),
	'api/user/:userid'                                 => array('api/user',                    'name' => 'api_user_id'),
	'api/user'                                         => array('api/user',                    'name' => 'api_user'),

		// collection
	'api/:appslug/collection/:namespace/abstract/:ref' => array('api/collection/abstract',     'name' => 'api_collection_abstract_ref'),
	'api/:appslug/collection/:namespace/abstract'      => array('api/collection/abstract',     'name' => 'api_collection_abstract'),
	'api/:appslug/collection/:namespace/drop'          => array('api/collection/defined/drop', 'name' => 'api_collection_drop'),
	'api/:appslug/collection/:namespace'               => array('api/collection/defined',      'name' => 'api_collection_defined'),
	'api/:appslug/collection'                          => array('api/collection',              'name' => 'api_collection'),

		// document
	'api/:appslug/document/:namespace/:ref/status'     => array('api/document/status',         'name' => 'api_document_status'),
	'api/:appslug/document/:namespace/:ref'            => array('api/document/defined',        'name' => 'api_document_ref'),
	'api/:appslug/document/:namespace'                 => array('api/document/',               'name' => 'api_document'),

		// library
	'api/:appslug/library/:filename/preset/:preset'    => array('api/library/preset',          'name' => 'api_library_preset'),
	'api/:appslug/library/:filename'                   => array('api/library',                 'name' => 'api_library_filename'),
	'api/:appslug/library'                             => array('api/library',                 'name' => 'api_library'),

		// app
	'api/app'                                          => array('api/app',                     'name' => 'api_app'),
	'api/job'                                          => array('api/job',                     'name' => 'api_job'),
	//'api/:appslug/admin/:userid'                       => array('api/app/admin',             'name' => 'api_app_admin'),
	'api/:appslug/job'                                 => array('api/app/job',                 'name' => 'api_app_job'),
	'api/:appslug/invite'                              => array('api/app/invite',              'name' => 'api_app_invite'),
	'api/:appslug/user/:userid'                        => array('api/app/user',                'name' => 'api_app_user'),
	'api/:appslug/user'                                => array('api/app/user',                'name' => 'api_app_user_get'),
	'api/:appslug'                                     => array('api/app/defined',             'name' => 'api_app_defined'),

);