
$.Tapioca.Collections.Files = Backbone.Collection.extend(
{
    urlString: 'library',
    fetched:   false,

    initialize: function( options )
    {
        this.appslug = options.appslug;
    },
    
    url: function()
    {
       return $.Tapioca.config.apiUrl + this.appslug + '/' + this.urlString;
    },

    model: $.Tapioca.Models.File,

    sorted: function( field )
    {
        if( _.isUndefined( field ))
            field = 'name';

        return _( this.toJSON() ).sortBy(function(user)
        {
            return user[ field ].toLowerCase();
        });
    },

    setCategories: function( attributes )
    {
        this.categories = this.categories || {};

        _.extend(this.categories, attributes);
        
    },

    parse: function( response )
    {
        this.setCategories( response.categories );

        this.fetched = true;

        return response.results;
    },

    isFetched: function()
    {
        return this.fetched;
    }
});