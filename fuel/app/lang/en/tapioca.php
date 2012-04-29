<?php

return array(

	/** Collection Exception Messages **/
	'collection_not_found'           => 'Collection ":collection" does not exist.',
	'collection_revision_not_found'  => 'Revison ":revision" does not exist in Collection ":collection"',
	'no_collection_selected'         => 'No collection is selected.',
	'collection_column_is_empty'     => ':column must not be empty.',
	'collection_already_exists'      => 'The collection name ":name" already exists.',
	'can_not_insert_collection_data' => 'Collection ":name" can not be update',
	'can_not_update_collection_revision' => 'Collection ":name" can not be update',

	/** Document Exception Messages **/
	'document_not_found'           => 'Document ":ref" does not exist in collection ":collection".',
	'document_revision_not_found'  => 'Revison ":revision" does not exist for Document ":ref" in collection ":collection".',
	'no_document_selected'         => 'No document is selected.',
	'document_column_is_empty'     => ':column must not be empty.',

	/** API Execption Messages **/
	'missing_required_params'      => 'Some required params are missing',
	'no_collections'               => 'No collections define yet',

	/** UI Texts **/
	'ui' => array(
		'user_account' => 'Account',
		'user_logout' => 'Logout'
	),
);
