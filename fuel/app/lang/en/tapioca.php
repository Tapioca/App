<?php

return array(

	'internal_server_error'  => 'Internal Server Error',
	'no_valid_token'         => 'Not valid token for delete',
	'token_expire'           => 'token has expired',
	
	/** General Exception Messages **/
	'account_not_activated'  => 'User has not activated their account.',
	'account_is_disabled'    => 'This account has been disabled.',
	'invalid_limit_attempts' => 'Auth Config Item: "limit.attempts" must be an integer greater than 0',
	'invalid_limit_time'     => 'Auth Config Item: "limit.time" must be an integer greater than 0',

	/** App Exception Messages **/
	'app_already_exists'      => 'The app name ":app" already exists.',
	'app_slug_invalid'        => 'The app slug ":app" is invalid.',
	'app_level_empty'         => 'You must specify a level of the app.',
	'app_name_empty'          => 'You must specify a name of the app.',
	'app_not_found'           => 'app ":app" does not exist.',
	'invalid_app_id'          => 'app ID must be a valid integer greater than 0.',
	'not_found_in_app_object' => '":field" does not exist in "app" object.',
	'no_app_selected'         => 'No app is selected to get from.',
	'user_already_in_app'     => 'The User is already in app ":app".',
	'user_not_in_app'         => 'The User is not in app ":app".',

	/** User Exception Messages **/
	'email_already_exists'            => 'That Email already exists.',
	'column_and_password_empty'       => ':column and Password can not be empty.',
	'email_and_password_empty'        => 'Email and Password can not be empty.',
	'column_is_empty'                 => ':column must not be empty.',
	'email_already_in_use'            => 'That email is already in use.',
	'invalid_old_password'            => 'Old password is invalid',
	'invalid_user_id'                 => 'User ID must be a valid integer greater than 0.',
	'no_user_selected'                => 'You must first select a user.',
	'no_user_selected_to_delete'      => 'No user is selected to delete.',
	'no_user_selected_to_get'         => 'No user is selected to get.',
	'not_found_in_user_object'        => '":field" does not exist in "user" object.',
	'password_empty'                  => 'Password can not be empty.',
	'user_already_enabled'            => 'The user is already enabled',
	'user_already_disabled'           => 'The user is already disabled',
	'user_not_found'                  => 'The user does not exist.',
	'user_deleted'                    => 'User have been deleted.',

	/** Attempts Exception Messages **/
    'login_ip_required'    => 'Login Id and IP Adress are required to add a login attempt.',
    'single_user_required' => 'Attempts can only be added to a single user, an array was given.',
    'user_suspended'       => 'You have been suspended from trying to login into account ":account" for :time minutes.',
    
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
	'document_failed_at_rules_validation' => 'document failed at rules validation',

	/** File Exception Messages **/
	'file_already_exists'          => 'The file name ":name" already exists.',
	'no_file_selected'             => 'No file is selected.',
	'fail_to_store_file'           => 'Tapioca can not store :filename into GridFs, error: :error',
	/** API Execption Messages **/
	'missing_required_params'      => 'Some required params are missing',
	'no_collections'               => 'No collections define yet',

	/** UI Texts **/
	'ui' => array(
		'user_account' => 'Account',
		'user_logout' => 'Logout'
	),

	'doc_status' => array(
		'not_translated' => 'not translated',
		'out_of_date'    => 'out of date',
		'offline'        => 'offline',
		'draft'          => 'draft',
		'published'      => 'published'
	)
);
