

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
        //$.Tapioca.Mediator.publish('section:highlight', document.location.href);
    },

    logout: $.Tapioca.Controllers.Session.Logout,

    account: $.Tapioca.Controllers.Session.Account,

    index: function() {},

    overview: function() {},

    admin: $.Tapioca.Controllers.Admin.Home,

    adminUser: $.Tapioca.Controllers.Admin.Users,

    adminUserEdit: $.Tapioca.Controllers.Admin.User,

    notFound: function( path )
    {
        // TODO: display a 404 page
        var msg = "Unable to find path: " + path;
        console.log(msg);
    }
});