

$.Tapioca.Router = Backbone.Router.extend({

    initialize: function(options)
    {
        var self = this;

        $.Tapioca.Session = new $.Tapioca.Models.Session();

        $.Tapioca.appView = new $.Tapioca.Views.App({
            model: $.Tapioca.Session
        });

        this.routes = options.routes;
    },

    // Generic action before display view
    before: function( route )
    {
        if($.Tapioca.view)
            $.Tapioca.view.close();
    },

    // Generic action after display view
    after: function( route )
    {
        $.Tapioca.Nanoscroller();

        // highlight active navigation tab
        var _channel = $.Tapioca.appslug + '::section::highlight';

        $.Tapioca.Mediator.publish( 'section::highlight' );
        $.Tapioca.Mediator.publish( _channel , document.location.href);
    },

    logout: $.Tapioca.Controllers.Session.Logout,

    account: $.Tapioca.Controllers.Session.Account,

    index: function() {},

    overview: function() {},

    admin: $.Tapioca.Controllers.Admin.Home,

    adminUser: $.Tapioca.Controllers.Admin.Users,

    adminUserEdit: $.Tapioca.Controllers.Admin.User,

    adminApp: $.Tapioca.Controllers.Admin.Apps,

    adminAppEdit: $.Tapioca.Controllers.Admin.App,

    appHome: $.Tapioca.Controllers.App.Home,

    appUsers: $.Tapioca.Controllers.AppAdmin.Users,

    appSettings: $.Tapioca.Controllers.AppAdmin.Settings,

    appCollectionHome: $.Tapioca.Controllers.Collection.Home,

    appCollectionRef: $.Tapioca.Controllers.Collection.Ref,

    appCollectionNew: $.Tapioca.Controllers.Collection.New,

    appCollectionEdit: $.Tapioca.Controllers.Collection.Edit,

    appLibrary: $.Tapioca.Controllers.Library.Home,

    appLibraryRef: $.Tapioca.Controllers.Library.Ref,

    notFound: function( path )
    {
        // TODO: display a 404 page
        var msg = "Unable to find path: " + path;
        console.log(msg);
    }
});