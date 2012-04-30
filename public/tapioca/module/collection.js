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
//	Collections.Views.Home  = vCollectionHome;
//	Collections.Views.Edit  = vCollectionEdit;

	var model = null,
		view  = null;

	// Subscription 'modules' for our views.
	mediator.subscribe('callCollectionHome', function(appslug, namespace)
	{
		model = tapioca.apps[appslug].models.get(namespace);
		model.fetch();
		
		if(view != null) view.close();
		view  = new vCollectionHome({
						model: model,
						forceRender:  true
					});
	});

	mediator.subscribe('callCollectionEdit', function(appslug, namespace)
	{
		model = tapioca.apps[appslug].models.get(namespace);

		if(view != null) view.close();
		view  = new vCollectionEdit({
						model: model
					});
	});

	mediator.subscribe('callCollectionAdd', function(appslug)
	{
		console.log('callCollectionAdd')
		console.log(tapioca.apps[appslug].models);

		if(view != null) view.close();
		view  = new vCollectionEdit({
						model: new Collections.Model({
							app_id: appslug
						})
					});

	});

	// Required, return the module for AMD compliance
	return Collections;

});
