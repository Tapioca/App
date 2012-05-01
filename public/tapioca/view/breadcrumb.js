define([
	'tapioca',
	'underscore',
	'backbone',
	'Mustache',
	'text!template/subnav/breadcrumb-item.html'
], function(tapioca, _, Backbone, Mustache, tItem)
{
	return Backbone.View.extend(
	{
		el: $('#breadcrumb'),
		
		tagName: 'li',

		initialize: function(model)
		{
			this.model = model;
			this.render();
		},
 
		render: function()
		{
			this.$el.html('');

			_.each(this.model.models, function(item)
			{
				console.log(item)
			}, this);

			return this;
		}
	});
});