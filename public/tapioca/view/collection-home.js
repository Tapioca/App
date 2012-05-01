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

		initialize: function(options)
		{
			this.render();
			this.model.bind('fetch', this.render, this);
		},

		render: function()
		{
			this.$el.html('');

			var _html = Mustache.render(tContent, this.model.toJSON());

			this.$el
				.html(_html)
				.nanoScroller();

			return this;
		},

		onClose: function()
		{
			this.model.unbind('fetch', this.render);
			//this.model.unbind('reset', this.render);
		}
	});

	return view;
});