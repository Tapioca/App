define([
	'tapioca',
	'backbone',
	'underscore',
	'aura/mediator'
	/*,
	'view/breadcrumb'
	*/
], function(tapioca, Backbone, _, mediator) //, vBreadcrumb) 
{
	// Create a new module
	var Subnav         = tapioca.module();

	Subnav.Views.Breadcrumb = Backbone.View.extend(
	{
		el: $('#breadcrumb'),
		
		tagName: 'li',

		initialize: function(model)
		{
			console.log('ok ??')
			//this.model = model;
			//this.render();
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

	var breadcrumb = new Subnav.Views.Breadcrumb();

	mediator.subscribe('callCollectionHome', function(appslug, namespace)
	{
		var app = tapioca.apps[appslug];
		var appName = app.name;
		model = app.models.get(namespace);
		var collectionName = model.get('name');

		console.log(appName, collectionName);

		console.log(tapioca.app.router.reverse('collectionHome'))
	});

	// Required, return the module for AMD compliance
	return Subnav;

});