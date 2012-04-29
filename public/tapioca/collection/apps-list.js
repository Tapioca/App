define([
	'backbone',
	'model/app-details'
], function(Backbone, AppDetails)
{
	return Backbone.Collection.extend(
	{
		model: AppDetails,
		url: '/api/group/'
	})
});