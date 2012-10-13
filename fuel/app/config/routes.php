<?php
return array(
	'_root_'     => 'app/index',   // The default route
	'_404_'      => 'welcome/404', // The main 404 route

	// FRONT	
	'app/(:any)' => 'app/index',
	'app'        => 'app/index',

	// API REST 

		// user
	'api/user/me'                     => array('api/user/me', 'name' => 'api_user_me'),
	'api/user/:userid'                => array('api/user',    'name' => 'api_user_id'),
	'api/user'                        => array('api/user',    'name' => 'api_user'),

		// app
	'api/app/:appslug/admin'          => array('api/app/admin', 'name' => 'api_app_admin'),
	'api/app/:appslug/user'           => array('api/app/user',  'name' => 'api_app_user'),
	'api/app/:appslug'                => array('api/app',       'name' => 'api_app'),


	'api/:app_slug/collection/:namespace/drop'   => array('api/collection/drop', 'name' => 'api_collection_drop'),
	'api/:app_slug/collection/:namespace'        => array('api/collection/', 'name' => 'api_collection_ref'),
	'api/:app_slug/collection'                   => array('api/collection/', 'name' => 'api_collection'),

	'api/:app_slug/document/:collection/:ref/status'  => array('api/document/status', 'name' => 'api_document_status'),
	'api/:app_slug/document/:collection/:ref'         => array('api/document/', 'name' => 'api_document_ref'),
	'api/:app_slug/document/:collection'              => array('api/document/', 'name' => 'api_document'),

	'api/:app_slug/group/team'         => array('api/group/team', 'name' => 'api_group_team'),
	'api/:app_slug/group'              => array('api/group/', 'name' => 'api_group'),

	'api/:app_slug/file/summary'       => array('api/file/summary', 'name' => 'api_file_summary'),
	'api/:app_slug/file/:filename'     => array('api/file/name', 'name' => 'api_file_name'),
	'api/:app_slug/file'               => array('api/file', 'name' => 'api_file'),

);