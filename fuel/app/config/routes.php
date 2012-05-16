<?php
return array(
	'_root_'  => 'app/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	
	//'log' => array(array('GET', new Route('log/index')), array('POST', new Route('log/in'))),
	'file/:app_slug/preview/:ref'  => array('app/file/preview', 'name' => 'file_ref_preview'),
	'file/:app_slug/download/:ref' => array('app/file/download', 'name' => 'file_ref_download'),
	'file/:app_slug/:ref'          => array('app/file', 'name' => 'file_ref'),

	'app/(:any)'                      => 'app/index',
	'app'                             => 'app/index',

	// API REST 
	'api/:app_slug/collection/:namespace/drop'   => array('api/collection/drop', 'name' => 'api_collection_drop'),
	'api/:app_slug/collection/:namespace'        => array('api/collection/', 'name' => 'api_collection_ref'),
	'api/:app_slug/collection'                   => array('api/collection/', 'name' => 'api_collection'),

	'api/:app_slug/document/:collection/:ref/status'  => array('api/document/status', 'name' => 'api_document_status'),
	'api/:app_slug/document/:collection/:ref'         => array('api/document/', 'name' => 'api_document_ref'),
	'api/:app_slug/document/:collection'              => array('api/document/', 'name' => 'api_document'),

	'api/:app_slug/group/team'         => array('api/group/team', 'name' => 'api_group_team'),
	'api/:app_slug/group'              => array('api/group/', 'name' => 'api_group'),

	'api/:app_slug/file/:ref'         => array('api/file', 'name' => 'api_file_ref'),
	'api/:app_slug/file'              => array('api/file', 'name' => 'api_file'),

);