
$.Tapioca.Collections.Collections = Backbone.Collection.extend(
{
	urlString: 'collection',

	initialize: function( options )
	{
		this.appslug = options.appslug + '/';
	},
	
    url: function()
    {
       return $.Tapioca.config.apiUrl + this.appslug + this.urlString;
    },

	model: $.Tapioca.Models.Collection,

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
});