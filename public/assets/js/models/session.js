
// Model Session extend Model User
// he define the current logged in user

$.Tapioca.Models.Session = $.Tapioca.Models.User.extend({

    apps: [], // user apps

    url: function()
    {
        return $.Tapioca.config.apiUrl + 'user/me';
    },

    isAdmin: function()
    {
        return this.get('admin');
    }
});
