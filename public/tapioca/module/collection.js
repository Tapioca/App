define([
	'tapioca',
	'backbone',
	'underscore',
	'aura/mediator',
	'collection/app-collections',
	'model/app-collection',
	'view/collection-home',
	'view/collection-edit'
], function(tapioca, Backbone, _, mediator, cCollection, mCollection, vCollectionHome, vCollectionEdit) 
{
	// Create a new module
	var Collections        = tapioca.module();

	Collections.Collection  = cCollection;	
	Collections.Model       = mCollection;
	Collections.Views.Home  = vCollectionHome;
	Collections.Views.Edit  = vCollectionEdit; 

	// Subscription 'modules' for our views.
	mediator.subscribe('callCollectionHome', function(appslug, namespace)
	{
		console.log(appslug)
		console.log(namespace)
/*
		var model = tapioca.apps[appslug].model;
			model.fetch();
*/
/**/
		var model = new Collections.Model({
			appslug: appslug,
			namespace: namespace
		});
		model.fetch();
/**/
/*
		if (app.TapiocaView) app.TapiocaView.close();
		this.TapiocaView = new TapiocaView({model: this.model});
		this.TapiocaView.render();
*/
	});

	// Required, return the module for AMD compliance
	return Collections;

});
