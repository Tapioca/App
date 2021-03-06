
(function($)
{
    // String manipulation extensions for Underscore.js javascript library. 
    // Mix in non-conflict functions to Underscore namespace
    // https://github.com/epeli/underscore.string
    _.mixin(_.str.exports());

    // Zombies! RUN!
    // Managing Page Transitions In Backbone Apps
    // http://lostechies.com/derickbailey/2011/09/15/zombies-run-managing-page-transitions-in-backbone-apps/
    Backbone.View.prototype.close = function()
    {
        if( this.onClose )
        {
            this.onClose();
        }

        $.Tapioca.Mediator.publish('search::active');

        this.remove();
        this.unbind();
    }

    // Length of Javascript Object
    // http://stackoverflow.com/questions/5223/length-of-javascript-object-ie-associative-array
    Object.size = function(obj)
    {
        var size = 0, key;
        for (key in obj)
        {
            if ( obj.hasOwnProperty( key ) ) ++size;
        }
        return size;
    };

    // add a wrapper to 401 when session expired
    Backbone.wrapError = function(onError, originalModel, options)
    {
        return function(model, resp)
        {
            resp = model === originalModel ? resp : model;

            if( resp.status === 401 )
            {
                $.Tapioca.Mediator.publish('user::notLoggedIn');
            }

            if (onError)
            {
                onError(originalModel, resp, options);
            }
            else
            {
                originalModel.trigger('error', originalModel, resp, options);
            }
        };
    };

    // App namespace
    $.Tapioca = typeof $.Tapioca === 'undefined' ? {} : $.Tapioca;

    $.Tapioca = {
        defaults: {
            models: ['Collections', 'Libraries'], // Models to load on user loggin per Apps
        },
        Today        : new Date(),         // use as reference, do not manipulate
        Session      : false,              // logged in user's model
        Apps         : false,              // admin's Apps collection
        Users        : false,              // admin's Users collection
        UserApps     : [],                 // logged in user's Apps collection  
        Collections  : {}, 
        Models       : {}, 
        Views        : {}, 
        Controllers  : {}, 
        Components   : {},
        view         : false,              // reference to the current view, allow a clean close
        appslug      : false,              // reference to the current app
        Tpl          : {},                 // Templates list array
        Data         : {},                 // Collections data
        appView      : false,              // reference to the app view when user is loggin
        locked       : [],                 // list of locked view (prevent admin to delete other admin update)
        routes       : {
            ''                                      : 'index',
            'app'                                   : 'overview',
            'app/logout'                            : 'logout',
            'app/account'                           : 'account',
            'app/admin'                             : 'admin',
            'app/admin/user'                        : 'adminUser',
            'app/admin/user/:uid'                   : 'adminUserEdit',
            'app/admin/app'                         : 'adminApp',
            'app/admin/app/:slug'                   : 'adminAppEdit',
            'app/admin/workers'                     : 'adminWorkers',
            'app/:appslug'                          : 'appHome',
            'app/:appslug/user'                     : 'appUsers',
            'app/:appslug/settings'                 : 'appSettings',
            'app/:appslug/library/:basename.:ext'   : 'appLibraryRef',
            'app/:appslug/library/c/:category'      : 'appLibraryCat',
            'app/:appslug/library'                  : 'appLibrary',
            'app/:appslug/collection/new'           : 'appCollectionNew',
            'app/:appslug/search'                   : 'appSearchResult',
            'app/:appslug/:namespace/edit'          : 'appCollectionEdit',
            'app/:appslug/:namespace/:ref'          : 'appCollectionRef',
            'app/:appslug/:namespace'               : 'appCollectionHome',
            '*path'                                 : 'notFound'
        }
    }

    // UI components

    if ($.browser.webkit)
    {
        $.Tapioca.vP            = '-webkit-';
        $.Tapioca.transitionEnd = 'webkitTransitionEnd';
    }
    else if ($.browser.msie)
    {
        $.Tapioca.vP            = '-ms-';
        $.Tapioca.transitionEnd = 'msTransitionEnd';
    }
    else if ($.browser.mozilla) 
    {
        $.Tapioca.vP            = '-moz-';
        $.Tapioca.transitionEnd = 'transitionend';
    }
    else if ($.browser.opera)
    {
        $.Tapioca.vP            = '-o-';
        $.Tapioca.transitionEnd = 'oTransitionEnd';
    }

        // form reset jQuery compliant

    $.fn.reset = function()
    {
        this[0].reset();

        return this;
    }

        // Easing

    $.extend($.easing,{
        easeOut:function (x, t, b, c, d) {
            return -c *(t/=d)*(t-2) + b;
        },
        easeOutCubic: function (x, t, b, c, d) {
            return c*((t=t/d-1)*t*t + 1) + b;
        },
        easeInOutQuad: function (x, t, b, c, d) {
            if ((t/=d/2) < 1) return c/2*t*t + b;
            return -c/2 * ((--t)*(t-2) - 1) + b;
        }
    });

        // Custom scrollbar

    var nanoOpts = {
        paneClass:    'track',
        contentClass: 'pane-content'
    };

    $.Tapioca.Nanoscroller = function()
    {
        $('#app-container').find('.nano').nanoScroller( nanoOpts );
    }

    $.fn.TapiocaNano = function()
    {
        this.find('.nano').nanoScroller( nanoOpts );

        return this;
    }

    // APP's global Init
    $.Tapioca.bootstrap = function(_config)
    {
        // merge default settings with server variable
        $.Tapioca.config = $.extend({}, $.Tapioca.defaults, _config || {});

        // prevent problem when Tapioca is install in subfolder
        var routes = {};

        for(var i in $.Tapioca.routes)
        {
            var index = $.Tapioca.config.bbRootUrl + i;

            routes[ index ] = $.Tapioca.routes[i];
        }

        // define app's routes
        $.Tapioca.app = new $.Tapioca.Router({
            routes: routes
        });

        var $body = $('html, body');

        // start the application
        Backbone.history.start({pushState: true, root: $.Tapioca.config.host});

        $.Tapioca.Dialog.init();

        // register commun partials templates
        var docStatusList = $.Tapioca.Components.Display.statusList();
        Handlebars.registerPartial('docStatusList', docStatusList);
        Handlebars.registerPartial('locale-list',   $.Tapioca.Tpl.components.locales );
        Handlebars.registerPartial('preview-edit',  $.Tapioca.Tpl.app.container.collection['preview-edit'] );
        Handlebars.registerPartial('tag-edit',      $.Tapioca.Tpl.components['tag-edit'] );

        // All navigation should be passed through the navigate method, 
        // to be processed by the router.  If the link has a data-bypass
        // attribute, bypass the delegation completely.
        $(document).on('click', 'a:not([data-bypass])', function(event)
        {
            // Get the anchor href and protcol
            var href = this.href;

            if( href.indexOf("javascript:") == 0 )
                return false;

            // open external link in a new window.
            if( href && href.slice(0, $.Tapioca.config.rootUrl.length) !== $.Tapioca.config.rootUrl)
            {
                window.open( href );
                return false;
            }
            else // inner app nav
            {
                // Stop the default event to ensure 
                // the link will not cause a page refresh.
                event.preventDefault();

                // remove trailing slash
                if(href.substr(-1) == '/')
                {
                    href = href.substr(0, href.length - 1);
                }

                var callback = function()
                {
                    // `Backbone.history.navigate` is sufficient for all Routers and will
                    // trigger the correct events.  The Router's internal `navigate` method
                    // calls this anyways.
                    Backbone.history.navigate(href, true);

                    // scroll to main navigation
                    // $body.animate({scrollTop: 180}, 600, 'easeOut');

                    // reset page's change notifiactions
                    $.Tapioca.BeforeUnload.clean();
                }

                // check if we set a dialog before we unload page
                if($.Tapioca.BeforeUnload.verify())
                {
                    $.Tapioca.Dialog.confirm(callback, { text: __('dialog.beforeunload')});
                }
                else
                {
                    callback();
                }
            }
        });
    };

    // Expose $.Tapioca to the global object
    // TODO: is that necessary as $.Tapioca 
    // is part of jQuery object ?
    window.$.Tapioca = $.Tapioca;

})(jQuery);
