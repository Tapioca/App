define([
	'tapioca',
	'backbone',
	'underscore',
	'aura/mediator'
], function(tapioca, Backbone, _, mediator) 
{
	// Create a new module
	var Documents         = tapioca.module();
	Documents.Collection  = Backbone.Collection.extend(
	{
		idAttribute: '_ref',
		initialize: function(appSlug, namespace)
		{
			this.appSlug   = appSlug;
			this.namespace = namespace;
		},
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.appSlug + '/document/' + this.namespace;
		}
	});

	Documents.Model       = Backbone.Model.extend(
	{
		idAttribute: '_ref',
		initialize: function(appSlug)
		{
			this.appSlug = appSlug;
		},
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.appSlug + '/document/' + this.namespace; // + '/' + this.get('_ref');
		},
		defaults:{
			'_ref': ''
		}
	});

	var model = null;

	var highlight = function(appslug, namespace)
	{
		$('#app-nav-collections-'+appslug).trigger('collection:highlight', namespace);
		$('#apps-nav').find('a.app-nav-header[data-app-slug="'+appslug+'"]').trigger('click');
	}

	mediator.subscribe('_callDocumentsList', function(slug, namespace)
	{
		if(!tapioca.apps[slug].documents[namespace])
		{
			tapioca.apps[slug].documents[namespace]       = new Documents.Collection(slug, namespace);
			tapioca.apps[slug].documents[namespace].model = Documents.Model;
		}
		

		var docs = tapioca.apps[slug].documents[namespace];
		docs.fetch();


/*
		model = tapioca.apps[appslug].models.get(namespace);
		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionHome({
						model: model,
						forceRender:  true
					});
*/
	});

	// Required, return the module for AMD compliance
	return Documents;

});
