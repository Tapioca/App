
$.Tapioca.Collections.Users = Backbone.Collection.extend(
{
    url: function()// endpoint REST
    {
        return $.Tapioca.config.apiUrl + 'user';
    }, 

    model: $.Tapioca.Models.User,

    sorted: function( field )
    {
        if( _.isUndefined( field ))
            field = 'name';

        return _( this.toJSON() ).sortBy(function(user)
        {
            return user[ field ].toLowerCase();
        });
    },

    parse: function( response )
    {
        return response.results;
    }
})