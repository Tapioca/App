define([
	'backbone',
	'underscore',
	'Mustache',
	'text!template/sidebar/apps-list-item.html'
], function(Backbone, _, Mustache, tAppsListItem)
{
	return Backbone.View.extend(
	{
		tagName: 'li',

		template: tAppsListItem,

		initialize: function()
		{
			this.model.bind('change', this.render, this);
			this.model.bind('destroy', this.close, this);
		},

		render: function(eventName)
		{
			var _html = Mustache.render(tAppsListItem, this.model.toJSON());
			
			this.$el.html(_html).attr('data-namespace', this.model.get('namespace'));
			
			return this;
		},

		onClose: function()
		{
			this.model.unbind('change', this.render);
			this.model.unbind('destroy', this.close);
		}
	});
});