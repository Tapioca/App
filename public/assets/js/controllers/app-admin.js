
$.Tapioca.Controllers.AppAdmin = {

    Users: function( appslug ) 
    {
        $.Tapioca.appslug = appslug;

        $.Tapioca.view = new $.Tapioca.Views.AppAdminUsers({
            model: $.Tapioca.UserApps[ appslug ].app
        }).render();
    },

    Settings: function( appslug )
    {
        $.Tapioca.appslug = appslug;

        var settings = $.Tapioca.UserApps[ appslug ].app;

        $.Tapioca.view = new $.Tapioca.Views.AppAdminSettings({
            model: settings
        }).render();
    }
};