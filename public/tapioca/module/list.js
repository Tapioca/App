define([
	'tapioca',
	'backbone',
	'underscore',
	'aura/mediator',
	'view/collection-home'
], function(tapioca, Backbone, _, mediator, vCollectionHome) 
{
	// Create a new module
	var List         = tapioca.module();
	List.Model       = Backbone.Model.extend(
	{
		idAttribute: '_ref'
	});
	List.Collection  = Backbone.Collection.extend(
	{
		idAttribute: '_ref',
		model: List.Model,
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

	var highlight = function(appslug, namespace)
	{
		$('#app-nav-collections-'+appslug).trigger('collection:highlight', namespace);
		$('#apps-nav').find('a.app-nav-header[data-app-slug="'+appslug+'"]').trigger('click');
	}

	mediator.subscribe('callCollectionHome', function(appslug, namespace, params)
	{
		var model     = tapioca.apps[appslug].models.get(namespace),
			documents = new List.Collection(appslug, namespace);

		highlight(appslug, namespace);

		if(!_.isUndefined(params.locale))
		{
			tapioca.apps[appslug].locale_working = params.locale;
		}

		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionHome({
						collection: documents,
						header: model.toJSON(),
						appslug: appslug,
						namespace: namespace,
						locale: tapioca.apps[appslug].locale_working,
						locales: tapioca.apps[appslug].locales
					});

		documents.fetch({ data: $.param({ mode: 'list'}) })
	});

	// Required, return the module for AMD compliance
	return List;

});
