
$.Tapioca.Collections.Apps = Backbone.Collection.extend(
{
	urlString: 'app',

	initialize: function( options )
	{
		// this.slug = this.get('slug');
	},
	
    url: function()
    {
       return $.Tapioca.config.apiUrl + 'app';
    },

	model: $.Tapioca.Models.App,

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