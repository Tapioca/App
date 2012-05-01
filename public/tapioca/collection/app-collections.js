define([
	'backbone'
], function(Backbone)
{
	var collection =  Backbone.Collection.extend(
	{
		idAttribute: 'namespace',
		initialize: function(appSlug)
		{
			this.appSlug = appSlug;
			//this.model = new AppDetails();
		},
		//model: mCollection,
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.appSlug + '/collection';
		}
	});

	return collection;
});