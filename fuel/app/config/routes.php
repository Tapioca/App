<?php
return array(
	'_root_'  => 'welcome/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),

	// API REST 
	'api/collection/(:namespace)' => array('api/collection/', 'name' => 'api_collection'),

);