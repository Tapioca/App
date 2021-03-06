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

/**
 * Casset: Convenient asset library for FuelPHP.
 *
 * @package    Casset
 * @version    v1.11
 * @author     Antony Male
 * @license    MIT License
 * @copyright  2011 Antony Male
 * @link       http://github.com/canton7/fuelphp-casset
 */

return array(
    'paths' => array(
        'core' => 'assets/',
        'library' => array(
            'path' => 'assets/lib/',
            'js_dir' => '',
        ),
        'less' => array(
            'path' => 'assets/less/',
            'css_dir' => '',
        ),
        'fuel' => array(
            'path' => Config::get('base_url'),
            'js_dir' => '',
        ),
    ),

    'groups' => array(
        'css' => array(
            'app' => array(
                'files' => array(
                    // 'class.css',
                    // 'layout.css',
                    // 'font-awesome.css',
                    // 'bootstrap.css',
                    // 'bootstrap-overload.css',
                    'class.css',
                    'layout.css',
                    'font-awesome.css',
                    'bootstrap.css',
                    'bootstrap-overload.css',
                    'ui.css',
                    'colorpicker.css',
                    'datepicker.css',
                    'redactor.css',
                    'jquery.fileupload-ui.css',
                    'schema-editor.css',
                )
            ),
            'install' => array(
                'files' => array(
                    'bootstrap.css',
                    'class.css',
                    'install.css'
                )
            )
        ),
        'js' => array(
            'app' => array(
                'files' => array(
                    'library::jquery/jquery-1.7.2.js',
                    // 'library::jquery/jquery-ui-1.8.21.custom.min.js',
                    'library::jquery/jquery-ui-1.9.1.custom.js',
                    'library::nanoscroller/jquery.nanoscroller.js',

                    'library::bootstrap/bootstrap-button.js',
                    'library::bootstrap/bootstrap-tooltip.js',
                    'library::bootstrap/bootstrap-tab.js',
                    'library::bootstrap/bootstrap-dropdown.js',

                    'library::underscore/underscore.js',
                    'library::underscore/underscore.string.js',
                    'library::underscore/underscore.date.js',
                    'library::underscore/underscore.fr.js',

                    'library::backbone/backbone.js',
                    'library::backbone/backbone-approuter.js',
                    'library::backbone/backbone.routefilter.js',
                    'library::backbone/backbone-appready.js',
                    'library::backbone/backbone-relational.js',
                    'library::backbone/backbone-nested.js',
                    'library::backbone/backbone.queryparams.js',

                    'library::handlebars/handlebars-1.0.rc.1.js',

                    'library::form2js/form2js.js',
                    
                    'library::redactor/redactor.js',

                    // 'library::lunr/lunr.js',

                    'library::fileupload/jquery.iframe-transport.js',
                    'library::fileupload/jquery.fileupload.js',
                    'library::fileupload/jquery.fileupload-ui.js',
                    'library::fileupload/jquery.fileupload-fp.js',
                    // 'library::fileupload/canvas-to-blob.js',
                    // 'library::fileupload/load-image.js',

                    'tapioca.js',
                    'bootstrap.js',

                    'modules/mediator.js',
                    'modules/dialog.js',
                    'modules/before-unload.js',
                    'modules/file-upload.js',
                    'modules/i18n.js',
                    'modules/form-factory.js',
                    
                    'models/tapioca.js',
                    'models/user.js',
                    'models/session.js',
                    'models/app.js',
                    'models/collection.js',
                    'models/abstract.js',
                    'models/document.js',
                    'models/file.js',
                    'models/search.js',

                    'collections/users.js',
                    'collections/apps.js',
                    'collections/collections.js',
                    'collections/abstracts.js',
                    'collections/files.js',
                    'collections/search.js',
                    
                    'views/app/index.js',
                    'views/app/nav/index.js',
                    'views/app/nav/user.js',
                    'views/app/nav/admin.js',
                    'views/app/nav/app.js',
                    'views/app/nav/app-collection.js',
                    'views/app/nav/search.js',

                    'views/content.js',
                    'views/form-view.js',
                    'views/login.js',
                    'views/user-profile.js',
                    
                    'views/admin/index.js',
                    'views/admin/user/list.js',
                    'views/admin/user/list-row.js',
                    'views/admin/user/edit.js',
                    'views/admin/app/list.js',
                    'views/admin/app/list-row.js',
                    'views/admin/app/edit.js',
                    'views/admin/app/team-row.js',
                    'views/admin/workers/index.js',

                    'views/app/container/home/index.js',

                    'views/app/container/search/index.js',

                    'views/app/container/user/index.js',
                    'views/app/container/user/index-row.js',

                    'views/app/container/settings/index.js',

                    'views/app/container/library/index.js',
                    'views/app/container/library/index-row.js',

                    'views/app/container/collection/index.js',
                    'views/app/container/collection/index-row.js',
                    'views/app/container/collection/edit.js',
                    'views/app/container/collection/document.js',
                    'views/app/container/collection/revisions.js',
                    'views/app/container/collection/doc-form.js',
                    'views/app/container/collection/embed-ref.js',
                    'views/app/container/collection/embed-file.js',

                    'views/app/container/library/edit.js',

                    'controllers/session.js',
                    'controllers/admin.js',
                    'controllers/app.js',
                    'controllers/app-admin.js',
                    'controllers/collection.js',
                    'controllers/library.js',
                    'controllers/search.js',

                    'components/form.js',
                    // 'components/file-upload.js',
                    // 'components/string.js',
                    // 'components/date.js',
                    'components/display.js',
                    'components/array.js',

                    'router.js',

                    'fuel::templates',
                    'fuel::i18n'
                )
            )
        )
    ),
    'combine' => true,
    'enabled' => true,
    'inline' => false
);