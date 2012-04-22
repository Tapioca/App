<?php
return array(
	'_root_'  => 'welcome/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	
	// API REST 
	'api/:app_slug/collection/:namespace/drop' => array('api/collection/drop', 'name' => 'api_collection_drop'),
	'api/:app_slug/collection/:namespace'      => array('api/collection/', 'name' => 'api_collection_ref'),
	'api/:app_slug/collection'                 => array('api/collection/', 'name' => 'api_collection'),

	'api/:app_slug/document/:collection/:ref'  => array('api/document/', 'name' => 'api_document_ref'),
	'api/:app_slug/document/:collection'       => array('api/document/', 'name' => 'api_document'),

);