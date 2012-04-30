define([
	'tapioca',
	'backbone',
	'underscore',
	'module/collection',
	'view/apps-list-item'
], function(tapioca, Backbone, _, Collections, vAppsListItem)
{
	return Backbone.View.extend(
	{
		initialize: function(options)
		{
			this.appSlug = options.appSlug;
			this.baseUri = 'app/'+this.appSlug;

			this.model.bind('reset', this.render, this); 	
			this.model.bind('add', this.renderItem, this);
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