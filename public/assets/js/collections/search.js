
$.Tapioca.Collections.Search = Backbone.Collection.extend(
{
    urlString: 'search',

    initialize: function( options )
    {
        this.appslug = options.appslug;
    },
    
    url: function()
    {
       return $.Tapioca.config.apiUrl + this.appslug + '/' + this.urlString;
    },

    model: $.Tapioca.Models.Search
});