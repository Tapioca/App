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
			console.log('initialize collection-home')

			// stupid hacks because i don't get which event to use!!
			if(options.forceRender)
			{
				this.render();
			}
			//_.bindAll(this, 'render');
			//this.model.bind('all', this.render);
			//this.model.bind('all', this.render);
			/**/
			this.model.bind('fetch', this.render, this);
			//this.model.bind('refresh', this.render, this);
			/**/
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

		onClose: function()
		{
			//this.model.unbind('change', this.render);
			//this.model.unbind('reset', this.render);
		}
	});

	return view;
});