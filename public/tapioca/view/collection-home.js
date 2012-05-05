define([
	'view/content',
	'Mustache',
	'text!template/content/collection-home.html'
], function(vContent, Mustache, tContent)
{
	var view = vContent.extend(
	{
		initialize: function(options)
		{
			this.render();
			this.model.bind('fetch', this.render, this);
		},

		render: function()
		{
			this.$el.html('');

			var _html = Mustache.render(tContent, this.model.toJSON());

			this.html(_html);
				
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