
$.Tapioca.Controllers.Admin = {

    Home: function() 
    {
        $.Tapioca.view = new $.Tapioca.Views.AdminIndex().render();
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
