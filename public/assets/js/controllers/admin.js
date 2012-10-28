
$.Tapioca.Controllers.Admin = {

    Home: function() 
    {
        $.Tapioca.view = new $.Tapioca.Views.AdminIndex().render();
    },

    Apps: function()
    {
        $.Tapioca.view = new $.Tapioca.Views.AdminAppList({
            collection: $.Tapioca.Apps
        }).render();
    },

    App: function( _slug )
    {
        var isNew = false;

        if( _slug != 'new' )
        {
            var app = $.Tapioca.Apps.get( _slug );

            if( _.isUndefined( app ))
            {
                // TODO: 404
                return;
            }
        }

        if(_slug == 'new')
        {
            var app = new $.Tapioca.Models.App();

            isNew   = true;
        }

        $.Tapioca.view = new $.Tapioca.Views.AdminAppEdit({
            model: app,
            isNew: isNew
        }).render();
    },

    Users: function()
    {
        $.Tapioca.view = new $.Tapioca.Views.AdminUserList({
            collection: $.Tapioca.Users
        }).render();
    },

    User: function( _uid )
    {
        var isNew = false;

        if( _uid != 'new' )
        {
            var user = $.Tapioca.Users.get( _uid );

            if( _.isUndefined( user ))
            {
                // TODO: 404
                return;
            }
        }

        if(_uid == 'new')
        {
            var user = new $.Tapioca.Models.User();

            isNew   = true;
        }

        $.Tapioca.view = new $.Tapioca.Views.AdminUserEdit({
            model: user,
            isNew: isNew
        }).render();
    }
}
