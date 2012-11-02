
$.Tapioca.Collections.Users = Backbone.Collection.extend(
{
    fetched: false,

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
        this.fetched = true;

        return response.results;
    },

    reload: function()
    {
        var userIds = [];

        for( var i in this.models )
        {
            userIds.push( this.models[ i ].id )
        }

        userIds = userIds.join(';');

        this.fetch({ data: {  set: userIds } });
    },

    isFetched: function()
    {
        return this.fetched;        
    }
})