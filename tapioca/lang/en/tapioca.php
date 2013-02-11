<?php
/**
 * Tapioca: Schema Driven Data Engine 
 * Flexible CMS build on top of MongoDB, FuelPHP and Backbone.js
 *
 * @package   Tapioca
 * @version   v0.8
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/App
 */

return array(

    'new_version_available'  => 'A Tapioca update is available. You are running Tapioca version <tt>:installed</tt>. The current version is :lastest. Visit <a href="http://tapioca.io">tapioca.io</a> for more information.',

    /** General Exception Messages **/
    'account_not_activated'  => 'User has not activated their account.',
    'account_is_disabled'    => 'This account has been disabled.',
    'invalid_limit_attempts' => 'Auth Config Item: "limit.attempts" must be an integer greater than 0',
    'invalid_limit_time'     => 'Auth Config Item: "limit.time" must be an integer greater than 0',

    /** App Exception Messages **/
    'app_already_exists'      => 'The app name ":app" already exists.',
    'app_slug_invalid'        => 'The app slug ":app" is invalid.',
    'app_key_invalid'         => 'The api key ":api" is invalid.',
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
    'not_valid_email'                 => 'You must provide a valid Email.',
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
    'invite_user'                     => 'Join :app group on Tapioca',


    /** Attempts Exception Messages **/
    'login_ip_required'    => 'Login Id and IP Adress are required to add a login attempt.',
    'single_user_required' => 'Attempts can only be added to a single user, an array was given.',
    'user_suspended'       => 'You have been suspended from trying to login into account ":account" for :time minutes.',
    
    /** Collection Exception Messages **/
    'collection_not_found'               => 'Collection ":collection" does not exist.',
    'collection_revision_not_found'      => 'Revison ":revision" does not exist in Collection ":collection"',
    'no_collection_selected'             => 'No collection is selected.',
    'collection_column_is_empty'         => ':column must not be empty.',
    'collection_already_exists'          => 'The collection name ":name" already exists.',
    'can_not_insert_collection_data'     => 'Collection ":name" can not be update',
    'can_not_update_collection_revision' => 'Collection ":name" can not be update',
    'not_found_in_collection_object'     => '":field" does not exist in "collection" object.',

    /** Document Exception Messages **/
    'document_not_found'                  => 'Document ":ref" does not exist in collection ":collection".',
    'document_revision_not_found'         => 'Revison ":revision" does not exist for Document ":ref" in collection ":collection".',
    'no_document_selected'                => 'No document is selected.',
    'document_column_is_empty'            => ':column must not be empty.',
    'document_failed_at_rules_validation' => 'document failed at rules validation',
    'not_found_in_document_object'        => '":field" does not exist in "document" object.',

    /** File Exception Messages **/
    'file_already_exists'          => 'The file ":name" already exists, maybe under another name.',
    'no_file_selected'             => 'No file is selected.',
    'fail_to_store_file'           => 'Tapioca can not store :filename into GridFs, error: :error',
    'file_not_found'               => 'file ":file" does not exist.',
    'file_basename_empty'          => 'You must specify a filename.',
    'preset_not_define'            => 'Preset nor define',
    
    /** API Execption Messages **/
    'missing_required_params'      => 'Some required params are missing',
    'no_collections'               => 'No collections define yet',
    'internal_server_error'        => 'Internal Server Error',
    'no_valid_token'               => 'Not valid token for delete',
    'token_expire'                 => 'token has expired',
    'permissions_denied'           => 'Permission denied to :capability',
    'access_not_allowed'           => 'Access not allowed',
    

    'doc_status' => array(
        'not_translated' => 'not translated',
        'out_of_date'    => 'out of date',
        'offline'        => 'offline',
        'draft'          => 'draft',
        'published'      => 'published'
    ),

    /** UI Texts **/
    'ui' => array(
        'user_account' => 'Account',
        'user_logout'  => 'Logout',
        'label' => array(
            'submit'             => 'Save changes',
            'cancel'             => 'Cancel',
            'saving'             => 'Saving stuff...',
            'add_user'           => 'Add new user',
            'add_app'            => 'Add new app',
            'app_settings'       => 'Settings',
            'app_users'          => 'Users',
            'app_workers'        => 'Workers',
            'app_apps'           => 'Applications',
            'pushed_at'          => 'Pushed at',
            'user_name'          => 'Username',
            'user_email'         => 'Email',
            'user_status'        => 'Status',
            'user_role'          => 'Role',
            'user_password'      => 'Password',
            'new_password'       => 'New password',
            'conf_password'      => 'Confirm',
            'send_password'      => 'Send password',
            'random_password'    => 'Random password',
            'is_tapp_admin'      => 'Grant Tapioca Admin',
            'tapp_admin'         => 'Admin',
            'not_activated'      => 'not activated',
            'activated'          => 'activated',
            'edit'               => 'edit',
            'delete'             => 'delete',
            'remove'             => 'remove',
            'select'             => 'select',
            'clone'              => 'clone',
            'preview'            => 'Preview',
            'edit_app_profile'   => 'Profile',
            'edit_app_user'      => 'Users',
            'edit_app_admin'     => 'Admins',
            'edit_app_locale'    => 'Locales',
            'edit_app_apikey'    => 'Api Key',
            'edit_app_mediatype' => 'Media type',
            'ext_whitelist'      => 'File extension allowed',
            'app_slug'           => 'App slug',
            'app_name'           => 'Name',
            'no_collections'     => 'No collections define yet',
            'add_collections'    => 'Add a collection',
            'app_library'        => 'Library',
            'add_file'           => 'Upload a file',
            'update_file'        => 'Update file',
            'app_documents'      => 'Documents',
            'add_document'       => 'Add a new document',
            'document_status'    => 'Status',
            'collection_edit'    => 'Edit collection schema',
            'collection_empty'   => 'Empty collection',
            'edit_desc'          => 'Description',
            'edit_schema'        => 'Schema',
            'edit_digest'        => 'Digest',
            'edit_hooks'         => 'Hooks',
            'edit_preview'       => 'Preview',
            'col_namespace'      => 'Namespace',
            'col_name'           => 'Name',
            'col_desc'           => 'Description',
            'col_status'         => 'Status',
            'col_status_draft'   => 'draft',
            'col_status_public'  => 'public',
            'col_status_private' => 'private',
            'col_preview'        => 'Preview',
            'library_all_files'  => 'All files',
            'library_image'      => 'Image',
            'library_video'      => 'Video',
            'library_document'   => 'Document',
            'library_other'      => 'Other',
            'categories'         => 'Categories',
            'tags'               => 'Tags',
            'all_tags'           => 'All tags',
            'filename'           => 'filename',
            'category'           => 'category',
            'no_file'            => 'no file',
            'by'                 => 'by',
            'worker_job'         => 'Job',
            'worker_perfom'      => 'Do Job',
            'label'              => 'label',
            'key'                => 'key',
            'url'                => 'url',
            'cannot_edit_admin'  => 'You can not edit another admin profile.',
        ),
        'dialog' => array(
            'beforeunload' => 'Are you sure you want to leave with out save?',
            'btn_yes'      => 'Yes',
            'btn_no'       => 'Cancel',
        ),
        'delete' => array(
            'question'        => 'Are you sure you want to delete %1$s from %2$s ?',
            'user'            => 'the users',
            'file'            => 'the library',
            'question_remove' => 'Are you sure you want to remove %1$s from group ?',
        ),
        'title' => array(
            'edit_account'    => 'Edit user account',
            'edit_app'        => 'Edit %1$s',
            'admin'           => 'Tapioca Admin',
            'admin_user'      => 'Tapioca Users',
            'admin_app'       => 'Tapioca Apps',
            'admin_workers'   => 'Tapioca Workers',
            'dashbord'        => 'Dashboard',
            'app_users'       => '%1s users',
            'new_collection'  => 'New collection',
            'edit_collection' => 'Edit %1s collection',
            'new_document'    => 'New document',
            'edit_document'   => 'Edit document'
        ),
        'session' => array(
            'user-profile'    => 'My Account',
            'edit_account'    => 'Public Profile',
            'edit_password'   => 'Change Password',
            'edit_apps'       => 'Edit Apps',
            'name'            => 'My username',
            'email'           => 'My email',
            'invite_gravatar' => 'Change your avatar at Gravatar.com.',
            'old_password'    => 'Old password',
            'new_password'    => 'New password',
            'conf_password'   => 'Confirm'
        ),
        'roles' => array(
            'master'           => 'Instance Admin',
            'super_admin'      => 'Super Admin',
            'admin'            => 'App Admin',
            'editor'           => 'Editor',
            'author'           => 'Author',
            'guest'            => 'Guest',
            '_REVOKED_ACCESS_' => 'Revoked user',
        ),
        'library' => array(
            'image'    => 'Image',
            'video'    => 'Video',
            'document' => 'Document',
            'other'    => 'Other',
        ),
        'rules' => array(
            'required'      => 'The %1$s field is required.',
            'matches'       => 'The %1$s field does not match the %2$s field.',
            'valid_email'   => 'The %1$s field must contain a valid email address.',
            'min_length'    => 'The %1$s field must be at least %2$s characters in length.',
            'max_length'    => 'The %1$s field must not exceed %2$s characters in length.',
            'exact_length'  => 'The %1$s field must be exactly %2$s characters in length.',
            'greater_than'  => 'The %1$s field must contain a number greater than %2$s.',
            'less_than'     => 'The %1$s field must contain a number less than %2$s.',
            'alpha'         => 'The %1$s field must only contain alphabetical characters.',
            'alpha_numeric' => 'The %1$s field must only contain alpha-numeric characters.',
            'alpha_dash'    => 'The %1$s field must only contain alpha-numeric characters, underscores, and dashes.',
            'numeric'       => 'The %1$s field must contain only numbers.',
            'integer'       => 'The %1$s field must contain an integer.'
        ),
        'jobs' => array(
            'status_waiting'  => 'waiting',
            'status_running'  => 'running',
            'status_failed'   => 'failed',
            'status_complete' => 'complete'
        )
    ),
);
