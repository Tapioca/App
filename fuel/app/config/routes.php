<?php
return array(
	'_root_'     => 'app/index',   // The default route
	'_404_'      => 'welcome/404', // The main 404 route

	// FRONT	
	'app/(:any)' => 'app/index',
	'app'        => 'app/index',

	// API REST 

		// log
	'api/log/out'                     => array('api/log/out', 'name' => 'api_log_out'),
	'api/log'                         => array('api/log',     'name' => 'api_log'),

		// user
	'api/user/me'                     => array('api/user/me', 'name' => 'api_user_me'),
	'api/user/:userid'                => array('api/user',    'name' => 'api_user_id'),
	'api/user'                        => array('api/user',    'name' => 'api_user'),

		// collection
	'api/:appslug/collection/:namespace/abstract' => array('api/collection/abstract', 'name' => 'api_collection_abstract'),
	'api/:appslug/collection/:namespace/drop'     => array('api/collection/drop',     'name' => 'api_collection_drop'),
	'api/:appslug/collection/:namespace'          => array('api/collection',          'name' => 'api_collection_ref'),
	'api/:appslug/collection'                     => array('api/collection',          'name' => 'api_collection'),

		// document
	'api/:appslug/document/:namespace/:ref/status'  => array('api/document/status', 'name' => 'api_document_status'),
	'api/:appslug/document/:namespace/:ref'         => array('api/document/',       'name' => 'api_document_ref'),
	'api/:appslug/document/:namespace'              => array('api/document/',       'name' => 'api_document'),

		// app
	'api/app'                     => array('api/app',       'name' => 'api_app_list'),
	'api/:appslug/admin/:userid'  => array('api/app/admin', 'name' => 'api_app_admin'),
	'api/:appslug/user/:userid'   => array('api/app/user',  'name' => 'api_app_user'),
	'api/:appslug'                => array('api/app',       'name' => 'api_app'),



	'api/:app_slug/group/team'         => array('api/group/team', 'name' => 'api_group_team'),
	'api/:app_slug/group'              => array('api/group/', 'name' => 'api_group'),

	'api/:app_slug/file/summary'       => array('api/file/summary', 'name' => 'api_file_summary'),
	'api/:app_slug/file/:filename'     => array('api/file/name', 'name' => 'api_file_name'),
	'api/:app_slug/file'               => array('api/file', 'name' => 'api_file'),

);