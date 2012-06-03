define([
	'tapioca',
	'backbone',
	'underscore',
	'aura/mediator',
	'view/document-edit'
], function(tapioca, Backbone, _, mediator, vDocumentEdit) 
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
		initialize: function(attrs, options)
		{
			this.appSlug   = options.appSlug;
			this.namespace = options.namespace;
		},
		urlRoot: '/api',
		url: function()
		{
			var url = this.urlRoot + '/' + this.appSlug + '/document/' + this.namespace;

			if(this.get('_ref') != null)
			{
				url += '/' + this.get('_ref');
			}

			return url; //this.urlRoot + '/' + this.appSlug + '/document/' + this.namespace + '/' + this.get('_ref');
		},
		defaults:{
			'_ref': null
		}
	});

	var model = null;

	var highlight = function(appslug, namespace)
	{
		$('#app-nav-collections-'+appslug).trigger('collection:highlight', namespace);
		$('#apps-nav').find('a.app-nav-header[data-app-slug="'+appslug+'"]').trigger('click');
	}

	mediator.subscribe('callDocumentRef', function(slug, namespace, ref, revision)
	{
		highlight(slug, namespace);

		var collectionDetails = tapioca.apps[slug].models.get(namespace);
		var doc 			  = new Documents.Model({_ref: ref}, {appSlug: slug, namespace: namespace});
		var fetchOptions      = { mode: 'edit'};

		if(!_.isUndefined(revision))
		{
			fetchOptions['revision'] = revision;
		}

		collectionDetails.fetch({
			success: function(model, response)
			{
				if(tapioca.view != null) tapioca.view.close();
				tapioca.view  = new vDocumentEdit({
								model: doc,
								schema: collectionDetails
							});

				doc.fetch({ data: $.param(fetchOptions) });
			}
		});
	});

	mediator.subscribe('callDocumentNew', function(slug, namespace)
	{
		highlight(slug, namespace);

		var collectionDetails = tapioca.apps[slug].models.get(namespace);

		collectionDetails.fetch({
			success: function(model, response)
			{
				if(tapioca.view != null) tapioca.view.close();
				tapioca.view  = new vDocumentEdit({
								model: new Documents.Model({}, {appSlug: slug, namespace: namespace}),
								schema: collectionDetails,
								appSlug: slug, 
								namespace: namespace,
								forceRender: true
							});
			}
		});
	});

	// Required, return the module for AMD compliance
	return Documents;

});
