define([
	'backbone',
	'underscore',
	'aura/mediator',
	'collection/apps-list',
	'view/apps-list',
	'modules'
], function(Backbone, _, mediator, cAppsList, vAppsList) 
{
	return Backbone.View.extend(
	{
		initialize: function()
		{
			var self = this;

			// get User's Groups
			this.AppsListing = new cAppsList();
			this.AppsListing.fetch(
			{
				success: function()
				{
					self.render();
				}
			});
		},
 
		render: function() 
		{
			var slugs = [];
			this.AppsListing.each(function(app)
			{
				var AppsListingView = new vAppsList({
					model: app
				});

				slugs.push(app.get('slug'));
				
				this.$el.append(AppsListingView.$el);

			}, this);

			mediator.publish('appsAvailable', slugs);
		}
	});
});