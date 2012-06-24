define([
	'tapioca',
	'backbone',
	'underscore',
	'aura/mediator',
	'view/file-home',
	'view/file-popin',
	'view/file-upload'
], function(tapioca, Backbone, _, mediator, vFileHome, vFilePopin, vFileUpload) 
{
	// Create a new module
	var Files         = tapioca.module();
	Files.Model       = Backbone.Model.extend(
	{
		idAttribute: 'filename',
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + appSlug + '/file' + '/' + this.get('filename');
		},
		defaults:{
			'filename': null
		}
	});
	Files.Collection  = Backbone.Collection.extend(
	{
		idAttribute: 'filename',
		initialize: function(options)
		{
			this.appSlug = options.appSlug;
		},
		model: Files.Model,
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.appSlug + '/file';
		}
	});

	var model = null,
		collection = null,
		publicStorage,
		appSlug;

	var highlight = function(appslug, namespace)
	{
		$('#app-nav-collections-'+appslug).trigger('collection:highlight', namespace);
		$('#apps-nav').find('a.app-nav-header[data-app-slug="'+appslug+'"]').trigger('click');
	}

	mediator.subscribe('callFileRef', function(appslug, namespace)
	{
		model = tapioca.apps[appslug].models.get(namespace);

		highlight(appslug, namespace);

		if(tapioca.view != null) tapioca.view.close();
		tapioca.view  = new vCollectionEdit({
						model: model
					});
	});

	mediator.subscribe('callFileList', function(appslug)
	{
		appSlug = appslug;

		collection = new Files.Collection({
							appSlug: appslug
						});

		var popion = new vFilePopin({
							collection: collection,
							el: $('#ref-popin-content')
						});

		collection.fetch();
	});

	mediator.subscribe('callFileHome', function(appslug)
	{
		if(tapioca.view != null) tapioca.view.close();

		publicStorage = tapioca.config.file.base_path+appslug;
		appSlug = appslug;

		collection = new Files.Collection({
							appSlug: appslug
						});

		tapioca.view  = new vFileHome({
							collection: collection,
							publicStorage: publicStorage,
							appSlug: appSlug
						});

		collection.fetch();
	});

	mediator.subscribe('callFileNew', function(appslug)
	{
		if(tapioca.view != null) tapioca.view.close();

		publicStorage = tapioca.config.file.base_path+appslug;
		appSlug = appslug;

		tapioca.view  = new vFileUpload({
							publicStorage: publicStorage,
							appSlug: appSlug
						});
	});

	// Required, return the module for AMD compliance
	return Files;

});
