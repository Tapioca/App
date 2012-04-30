<?php
return array(
	'_root_'  => 'app/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	
	//'log' => array(array('GET', new Route('log/index')), array('POST', new Route('log/in'))),
	'app/(:any)' => 'app/index',
	'app'        => 'app/index',

	// API REST 
	'api/:app_slug/collection/:namespace/drop'   => array('api/collection/drop', 'name' => 'api_collection_drop'),
	'api/:app_slug/collection/:namespace'        => array('api/collection/', 'name' => 'api_collection_ref'),
	'api/:app_slug/collection'                   => array('api/collection/', 'name' => 'api_collection'),

	'api/:app_slug/document/:collection/:ref/status'  => array('api/document/status', 'name' => 'api_document_status'),
	'api/:app_slug/document/:collection/:ref'         => array('api/document/', 'name' => 'api_document_ref'),
	'api/:app_slug/document/:collection'              => array('api/document/', 'name' => 'api_document'),

	'api/:app_slug/group/team'         => array('api/group/team', 'name' => 'api_group_team'),
	'api/:app_slug/group'              => array('api/group/', 'name' => 'api_group'),

);