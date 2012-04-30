define([
	'order!jquery',
	'order!nanoScroller', 
	'backbone',
	'Mustache',
	'text!template/content/collection-home.html'
], function($, nanoScroller, Backbone, Mustache, tContent)
{
	var view = Backbone.View.extend(
	{
		el: $('#app-content'),

		initialize: function()
		{
			console.log('initialize collection-home')
			this.model.bind('change', this.render, this);
			this.model.bind('reset', this.render, this);
		},

		render: function()
		{
			this.$el.html('');

			var _html = Mustache.render(tContent, this.model.toJSON());

			var _options = {
				classPane: 'track',
				contentSelector: 'div.pane-content'
			};

			this.$el
				.html(_html)
				.nanoScroller(_options);

			return this;
		},

		close: function()
		{
			this.$el.unbind();
			this.$el.empty();
		}
	});

	return view;
});