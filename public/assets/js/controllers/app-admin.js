
$.Tapioca.Controllers.AppAdmin = {

    Users: function( appslug ) 
    {
        $.Tapioca.appslug = appslug;

        var users = $.Tapioca.UserApps[ appslug ].users;

        if( !users.isFetched() )
        {
            users.reload();
        }

        $.Tapioca.view = new $.Tapioca.Views.AppAdminUsers({
            collection: users
        }).render();
    },
};