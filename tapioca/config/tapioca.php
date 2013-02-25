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
 *
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your tapioca/config/ENV folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(

    'version_url' => 'https://raw.github.com/Tapioca/App/API_v2/VERSION',
    'skip_update' => false,

    'default_password' => 'azerty',

    'date' => array(
        'timezone' => 'Europe/Paris',
        'format'   => '%d/%m/%G'
    ),

    'mailer' => array(
        'email' => 'robot@tapiocapp.com',
        'name'  => 'Tapioca Robot',
    ),


    /*
     * Collections Names
     */
    'collections' => array(
        'users'           => 'users',
        'apps'            => 'apps',
        'users_suspended' => 'users_suspended',
        'collections'     => 'collections',
        'documents'       => 'documents',
        'files'           => 'files',
        'deletes'         => 'deletes',
        'previews'        => 'previews',
        'invitaions'      => 'invitaions',
        'queue'           => 'queue'
    ),

    /*
     * Session keys
     */
    'session' => array(
        'user'     => 'tapioca_user',
        'provider' => 'tapioca_provider',
    ),

    /*
     * Store each app's data 
     * in a separate Database
     */

    'multiDb' => false,

    /*
     * API key generator
     */

    'api' => array(
        'salts' => array(
            'Bull In The Heather',
            'Crack Rock Steady',
            'Famous Friends and Fashion Drunks',
            'And We Thought Nation States Were A Bad Idea',
            'Sing Along With the Patriotic Punks',
        ),
        'db_prefix' => ''
    ),

    /*
     * Preview TTL
     */

    'previewLimit' => 172800, // 2 days

    /*
     * Delete token TTL
     */

    'deleteToken' => 600, // 10 minutes

    /*
     * Archived Jobs TTL
     */

    'cleanQueue' => 172800, // 2 days

    /*
     * Background Workers
     * use Mongo|Resque
     */

    'worker' => 'Mongo', 
    
    /*
     * Remember Me settings
     */
    'remember_me' => array(

        /**
         * Cookie name credentials are stored in
         */
        'cookie_name' => 'tapioca_rm',

        /**
         * How long the cookie should last. (seconds)
         */
        'expire' => 1209600, // 2 weeks
    ),

    /**
     * Limit Number of Failed Attempts
     * Suspends a login/ip combo after a # of failed attempts for a set amount of time
     */
    'limit' => array(

        /**
         * enable limit - true/false
         */
        'enabled' => true,

        /**
         * number of attempts before suspensions
         */
        'attempts' => 3,

        /**
         * suspension length - minutes
         */
        'time' => 3,
    ),

    /*
     * Locales
     */
    'locales' => array(
        'default' => array(
            'key'     => 'fr_FR',
            'label'   => 'français / France',
            'default' => true
        )
    ),

    /*
     * Default documents/collections status
     */
    'status' => array(
        array(
            -2,
            'not_translated',
            'label-warning'
        ),
        array(
            -1,
            'out_of_date',
            ''
        ),
        array(
            0,
            'offline',
            'label-important'
        ),
        array(
            1,
            'draft',
            'label-info'
        ),
        array(
            100,
            'published',
            'label-success'
        )
    ),

    /*
     * required fileds
     */
    'validation' => array(
        'collection' => array(
            'summary' => array(
                'namespace',
                'name',
                'status'
            ),
            'data' => array(
                'schema'
            )
        )
    ),

    'collection' => array(
        'dispatch' => array(
            'summary' => array(
                'namespace',
                'namespace-suggest',
                'name',
                'desc',
                'status',
                'preview', 
                'digest'
            ),
            'data' => array(
                'schema', 
                'digest',
                'indexes',
                'hooks',
                'template'
            )
        ),
        'status' => array(
            'public',
            'private',
            'draft'
        )
    ),

    /*
     * Cast
     * fields type that needs to cast for mongodb
     */

    'cast' => array(
        'date',
        'number'
    ),

    /*
     * Cast
     * fields type that needs to cast for mongodb
     */

    'dependencies' => array(
        'dbref',
        'file',
    ),

    /*
     * Upload
     */

    'upload' => array(
        'path'                => APPPATH.'tmp',
        'storage'             => PUBPATH.'files'.DIRECTORY_SEPARATOR,
        'public'              => Config::get('base_url').'files'.DIRECTORY_SEPARATOR,
        'field'               => 'tappfile',
        'randomize'           => true,
        'fileinfo_magic_path' => '',
        'ext_whitelist'       => array('jpg', 'jpeg', 'gif', 'png', 'flv', 'mp4', 'ogv', 'doc', 'pdf', 'zip')
    ),

    /*
     * File by minetype
     */

    'file_types' => array(
        'image' => array(
            'image/bmp', 
            'image/x-windows-bmp',
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/x-png',
            'image/tiff',   
        ),
        'video' => array(
            'video/mpeg',
            'video/mp4',
            'application/ogg',
            'video/x-flv',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-sgi-movie',
            'video/vnd.rn-realvideo' // LOL
        ),
        'document' => array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/excel',
            'application/vnd.ms-excel',
            'application/msexcel',
            'application/powerpoint',
            'application/vnd.ms-powerpoint',
            'text/richtext',
            'text/rtf'
        )

    ),

    /*
     * Capabilities and Roles
     * wordpress inspired permissions
     */

    'capabilities' => array(
        // instance admin capabilities
        'list_users',
        'create_users',
        'edit_users',
        'delete_users',
        'disable_users',
        'promote_users',
        'read_users',

        'list_apps',
        'create_apps',
        'delete_apps',
        'disable_apps',

        'list_jobs',

        'manage_settings',
        'update_core',

        'export',
        'import',

        // app specific capabilities
        'app_edit_settings',

        'app_invite_users',
        'app_remove_users',
        'app_promote_users',

        'app_list_jobs',

        'app_list_collections',
        'app_read_collections_public',
        'app_read_collections_private',
        'app_read_collections_draft',
        'app_create_collections',
        'app_delete_collections',
        'app_empty_collections',
        'app_edit_collections',

        'app_list_documents',
        'app_read_documents',
        'app_create_documents',
        'app_edit_documents',
        'app_publish_documents',
        'app_delete_documents',
        'app_delete_published_documents',

        'app_edit_others_documents',
        'app_publish_others_documents',
        'app_delete_others_documents',
        'app_delete_others_published_documents',

        'list_files',
        'upload_files',
        'delete_files',
        'edit_files',
        'delete_others_files',
        'edit_others_files',

    ),

    // order is important!!
    'roles' => array(
        'master',
        'super_admin',
        'admin',
        'editor',
        'author',
        'guest',
    ),

    'default_premissions' => array(
        'master' => '*',
        'super_admin' => array(
            'list_users',
            'create_users',
            'edit_users',
            // 'delete_users',
            'disable_users',
            // 'promote_users',
            'read_users',

            'list_apps',
            'create_apps',
            'read_apps',
            'delete_apps',
            'disable_apps',

            'list_jobs',

            'app_edit_settings',

            'app_invite_users',
            'app_remove_users',
            'app_promote_users',

            'app_list_jobs',

            'app_list_collections',
            'app_read_collections_public',
            'app_read_collections_private',
            'app_read_collections_draft',
            'app_create_collections',
            'app_delete_collections',
            'app_empty_collections',
            'app_edit_collections',

            'app_list_documents',
            'app_read_documents',
            'app_create_documents',
            'app_edit_documents',
            'app_publish_documents',
            'app_delete_documents',
            'app_delete_published_documents',

            'app_edit_others_documents',
            'app_publish_others_documents',
            'app_delete_others_documents',
            'app_delete_others_published_documents',

            'list_files',
            'upload_files',
            'delete_files',
            'edit_files',
            'delete_others_files',
            'edit_others_files',
        ),
        'admin' => array(
            'list_users',
            'read_users',

            'list_apps',
            'read_apps',

            'app_edit_settings',

            'app_invite_users',
            'app_remove_users',
            'app_promote_users',

            'app_list_jobs',

            'app_list_collections',
            'app_read_collections_public',
            'app_read_collections_private',
            'app_read_collections_draft',
            'app_create_collections',
            'app_delete_collections',
            'app_empty_collections',
            'app_edit_collections',

            'app_list_documents',
            'app_read_documents',
            'app_create_documents',
            'app_edit_documents',
            'app_publish_documents',
            'app_delete_documents',
            'app_delete_published_documents',

            'app_edit_others_documents',
            'app_publish_others_documents',
            'app_delete_others_documents',
            'app_delete_others_published_documents',

            'list_files',
            'upload_files',
            'delete_files',
            'edit_files',
            'delete_others_files',
            'edit_others_files',
        ),
        'editor' => array(
            'list_users',
            'read_users',

            'list_apps',
            'read_apps',

            'app_list_collections',
            'app_read_collections_public',
            'app_read_collections_private',
            // 'app_create_collections',
            // 'app_delete_collections',
            'app_empty_collections',
            // 'app_edit_collections',

            'app_list_documents',
            'app_read_documents',
            'app_create_documents',
            'app_edit_documents',
            'app_publish_documents',
            'app_delete_documents',
            'app_delete_published_documents',

            'app_edit_others_documents',
            'app_publish_others_documents',
            'app_delete_others_documents',
            'app_delete_others_published_documents',

            'list_files',
            'upload_files',
            'delete_files',
            'edit_files',
            'delete_others_files',
            'edit_others_files',
        ),
        'author' => array(
            'list_users',
            'read_users',

            'list_apps',
            'read_apps',

            'app_list_collections',
            'app_read_collections_public',
        
            'app_list_documents',
            'app_read_documents',
            'app_create_documents',
            'app_edit_documents',

            'list_files',
            'upload_files',
            'delete_files',
            'edit_files',
            'edit_others_files',
        ),
        'guest' => array(
            'list_users',
            'read_users',

            'list_apps',
            'read_apps',

            'app_list_collections',
            'app_read_collections_public',
        
            'app_list_documents',
            'app_read_documents',
            
            'list_files',
        )
    )

);
