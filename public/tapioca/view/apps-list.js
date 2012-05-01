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

			this.currentHighlight = null;

			this.model.bind('reset', this.render, this);
			this.model.bind('add', this.renderItem, this);
		},

		events:
		{
			'collection:highlight': 'highlight'
		},

		highlight: function(event, collection)
		{
			if(this.currentHighlight != collection)
			{
				this.currentHighlight = collection;
				
				this.$el.find('li').removeClass('active');
				this.$el.find('li[data-namespace="' + collection + '"]').addClass('active');
			}
		},
 
		render: function()
		{
			_.each(this.model.models, this.renderItem, this);

			return this;
		},

		renderItem: function(collection)
		{
			// remove default message
			this.$el.find('li.app-nav-collections-empty').remove();

			collection.set('base_uri', this.baseUri);
			this.$el.prepend(new vAppsListItem({model: collection}).render().el);
		},

		onClose: function()
		{
			this.model.unbind('reset', this.render);
			this.model.unbind('add', this.close);
		}
	});
});