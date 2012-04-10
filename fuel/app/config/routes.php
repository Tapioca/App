<?php
return array(
	'_root_'  => 'welcome/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),

	// API REST 
	'api/collection/summary/(:namespace).json' => array('api/collection/summary', 'name' => 'api_collection_summary'),
	'api/collection/(:namespace).json' => array('api/collection/', 'name' => 'api_collection'),

);