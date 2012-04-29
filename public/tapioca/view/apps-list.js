define([
	'globals',
	'backbone',
	'underscore',
	'collection/app-collections',
	'view/apps-list-item'
], function(globals, Backbone, _, cAppCollections, vAppsListItem)
{
	return Backbone.View.extend(
	{
		initialize: function(options)
		{
			this.appSlug = options.appSlug;
			this.baseUri = globals.base_uri+this.appSlug;
			this.model   = new cAppCollections(this.appSlug);
			
			var self     = this;

			this.model.fetch({
				success: function()
				{
					self.render();
				}
			});

			this.model.bind('reset', this.render, this); 	
			this.model.bind('add', this.renderItem);
		},
 
		render: function()
		{
			this.$el.html('');

			_.each(this.model.models, this.renderItem, this);

			return this;
		},

		renderItem: function(collection)
		{
			collection.set('base_uri', this.baseUri);
			this.$el.append(new vAppsListItem({model: collection}).render().el);
		}
	});
});