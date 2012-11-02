
$.Tapioca.Collections.Abstracts = Backbone.Collection.extend(
{
    fetched:   false,
    urlString: null,

    initialize: function( options )
    {
        this.appslug   = options.appslug;
        this.namespace = options.namespace;
        this.urlString = this.appslug + '/collection/' + this.namespace + '/abstract';
    },
    
    url: function()
    {
       return $.Tapioca.config.apiUrl + this.urlString;
    },

    model: $.Tapioca.Models.Abstract,

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

    isFetched: function()
    {
        return this.fetched;
    }
});