define([
	'tapioca',
	'backbone',
	'underscore',
	'subrouter',
	'aura/mediator',
	'collection/app-collections',
	'model/app-collection',
	'view/collection-home',
	'view/collection-edit'
], function(tapioca, Backbone, _, subrouter, mediator, cCollection, mCollection, vCollectionHome, vCollectionEdit) 
{
	// Create a new module
	var Collections        = tapioca.module();

	Collections.Collection  = cCollection;	
	Collections.Model       = mCollection;

	var model = null;

	mediator.subscribe('callCollectionHome', function(appslug, namespace)
	{
		model = tapioca.apps[appslug].models.get(namespace);
		
		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionHome({
						model: model,
						forceRender:  true
					});
	});

	mediator.subscribe('callCollectionEdit', function(appslug, namespace)
	{
		model = tapioca.apps[appslug].models.get(namespace);

		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionEdit({
						model: model
					});
	});

	mediator.subscribe('callCollectionAdd', function(appslug)
	{
		console.log('callCollectionAdd')
		console.log(tapioca.apps[appslug].models);

		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionEdit({
						model: new Collections.Model({
							app_id: appslug
						})
					});

	});

	// Required, return the module for AMD compliance
	return Collections;

});
