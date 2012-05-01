define([
	'tapioca',
	'backbone',
	'underscore',
	'aura/mediator',
	'view/collection-home',
	'view/collection-edit'
], function(tapioca, Backbone, _, mediator, vCollectionHome, vCollectionEdit) 
{
	// Create a new module
	var Collections         = tapioca.module();
	Collections.Collection  = Backbone.Collection.extend(
	{
		idAttribute: 'namespace',
		initialize: function(appSlug)
		{
			this.appSlug = appSlug;
			//this.model = new AppDetails();
		},
		//model: mCollection,
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.appSlug + '/collection';
		}
	});

	Collections.Model       = Backbone.Model.extend(
	{
		idAttribute: 'namespace',
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.get('app_id') + '/collection' + '/' + this.get('namespace');
		},
		defaults:{
			'name': '',
			'namespace': null,
			'app_id': null,
			'desc': '',
			'status': 1,
			'structure': '',
			'summary': ''
		}
	});

	var model = null;

	var highlight = function(appslug, namespace)
	{
		$('#app-nav-collections-'+appslug).trigger('collection:highlight', namespace);
		$('#apps-nav').find('a.app-nav-header[data-app-slug="'+appslug+'"]').trigger('click');
	}

	mediator.subscribe('callCollectionHome', function(appslug, namespace)
	{
		model = tapioca.apps[appslug].models.get(namespace);

		highlight(appslug, namespace);

		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionHome({
						model: model,
						forceRender:  true
					});
	});

	mediator.subscribe('callCollectionEdit', function(appslug, namespace)
	{
		model = tapioca.apps[appslug].models.get(namespace);

		highlight(appslug, namespace);

		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionEdit({
						model: model
					});
	});

	mediator.subscribe('callCollectionAdd', function(appslug)
	{
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
