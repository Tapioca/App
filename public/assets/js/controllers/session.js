
$.Tapioca.Controllers.Session = {

    Account: function() 
    {
        $.Tapioca.view = new $.Tapioca.Views.UserProfile({
            model: $.Tapioca.Session
        });

        $.Tapioca.view.render();
    },

    Logout: function()
    {
        var _url = $.Tapioca.config.apiUrl + 'log/out';

        $.get(_url, function(data) 
        {
            $.Tapioca.Mediator.publish('user::notLoggedIn');

            Backbone.history.navigate( $.Tapioca.config.appUrl );
        });
    }
}
