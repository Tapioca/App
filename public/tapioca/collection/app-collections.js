define([
	'backbone',
	'model/app-collection'
], function(Backbone, AppDetails)
{
	return Backbone.Collection.extend(
	{
		initialize: function(appSlug)
		{
			this.appSlug = appSlug;
    	},
		model: AppDetails,
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.appSlug + '/collection';
		},
	})
});