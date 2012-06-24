define([
	'tapioca',
	'aura/mediator'
], function(tapioca, mediator) //, vBreadcrumb) 
{
	var app,
		build = function(_structure)
		{
			var total = _structure.length;
			var html  = '';

			for(var i = -1; ++i < total;)
			{
				var s = _structure[i];
				
				html += '<li';
				
				if(s.is_active)
				{
					html += ' class="active"';
				}

				html += '><a href="/'+s.url+'">'+s.name+'</a>';

				if(!s.is_active)
				{
					html += '<span class="divider">/</span>';
				}

				html += '</li>';
			}

			document.getElementById('breadcrumb').innerHTML = html;
			
		},
		makeObject = function(_appslug, _home)
		{
			app = tapioca.apps[_appslug];
			var breadcrumb = [
				{
					url: 'app',
					name: app.name,
					is_active: (_home) ? _home : false
				}
			];

			return breadcrumb;
		};

	mediator.subscribe('callDocumentNew', function(appslug, namespace)
	{
		var breadcrumb = makeObject(appslug);
		var model      = app.models.get(namespace);
		var routeHome  = tapioca.app.router.reverse('collectionHome');
		var routeEdit  = tapioca.app.router.reverse('documentNew');
		
		breadcrumb.push({
				url: tapioca.app.router.createUri(routeHome, [appslug, namespace]),
				name: model.get('name'),
				is_active: false
			});

		breadcrumb.push({
				url: tapioca.app.router.createUri(routeEdit, [appslug, namespace]),
				name: 'Compose new document',
				is_active: true
			});

		build(breadcrumb);
	});

	mediator.subscribe('callDocumentRef', function(appslug, namespace, ref)
	{
		var breadcrumb = makeObject(appslug);
		var model      = app.models.get(namespace);
		var routeHome  = tapioca.app.router.reverse('collectionHome');
		var routeEdit  = tapioca.app.router.reverse('documentRef');
		
		breadcrumb.push({
				url: tapioca.app.router.createUri(routeHome, [appslug, namespace]),
				name: model.get('name'),
				is_active: false
			});

		breadcrumb.push({
				url: tapioca.app.router.createUri(routeEdit, [appslug, namespace, ref]),
				name: 'Edit document',
				is_active: true
			});

		build(breadcrumb);
	});

	mediator.subscribe('callCollectionHome', function(appslug, namespace)
	{
		var breadcrumb = makeObject(appslug);
		var model      = app.models.get(namespace);
		var route      = tapioca.app.router.reverse('collectionHome');
		
		breadcrumb.push({
				url: tapioca.app.router.createUri(route, [appslug, namespace]),
				name: model.get('name'),
				is_active: true
			});

		build(breadcrumb);
	});

	mediator.subscribe('callCollectionEdit', function(appslug, namespace)
	{
		var breadcrumb = makeObject(appslug);
		var model      = app.models.get(namespace);
		var routeHome  = tapioca.app.router.reverse('collectionHome');
		var routeEdit  = tapioca.app.router.reverse('collectionEdit');

		breadcrumb.push({
				url: tapioca.app.router.createUri(routeHome, [appslug, namespace]),
				name: model.get('name'),
				is_active: false
			});

		breadcrumb.push({
				url: tapioca.app.router.createUri(routeEdit, [appslug, namespace]),
				name: 'Edit', // TODO: i18n
				is_active: true
			});

		build(breadcrumb);
	});


	mediator.subscribe('callCollectionAdd', function(appslug)
	{
		var breadcrumb = makeObject(appslug);
		var route      = tapioca.app.router.reverse('collectionAdd');
		
		breadcrumb.push({
				url: tapioca.app.router.createUri(route, [appslug]),
				name: 'Add', // TODO: i18n
				is_active: true
			});

		build(breadcrumb);
	});

	mediator.subscribe('callFileHome', function(appslug)
	{
		var breadcrumb = makeObject(appslug);
		var route      = tapioca.app.router.reverse('fileHome');
		
		breadcrumb.push({
				url: tapioca.app.router.createUri(route, [appslug]),
				name: 'Library', // TODO: i18n
				is_active: true
			});

		build(breadcrumb);
	});

	mediator.subscribe('callFileNew', function(appslug)
	{
		var breadcrumb = makeObject(appslug);
		var routeHome  = tapioca.app.router.reverse('fileHome');
		var routeNew   = tapioca.app.router.reverse('fileNew');

		breadcrumb.push({
				url: tapioca.app.router.createUri(routeHome, [appslug]),
				name: 'Library', // TODO: i18n
				is_active: false
			});

		
		breadcrumb.push({
				url: tapioca.app.router.createUri(routeNew, [appslug]),
				name: 'Upload', // TODO: i18n
				is_active: true
			});

		build(breadcrumb);
	});


	// Required, return the module for AMD compliance
	return true;

});