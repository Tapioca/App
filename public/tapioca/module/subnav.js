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

	Subnav.Breadcrumb = function(_structure)
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
		
	};

	mediator.subscribe('callCollectionHome', function(appslug, namespace)
	{
		var app   = tapioca.apps[appslug];
		var model = app.models.get(namespace);
		var route = tapioca.app.router.reverse('collectionHome');
		
		var breadcrumb = [
			{
				url: 'app',
				name: app.name,
				is_active: false
			},
			{
				url: tapioca.app.router.createUri(route, [appslug, namespace]),
				name: model.get('name'),
				is_active: true
			}			
		];

		Subnav.Breadcrumb(breadcrumb);
	});

	mediator.subscribe('callCollectionEdit', function(appslug, namespace)
	{
		var app   = tapioca.apps[appslug];
		var model = app.models.get(namespace);
		var routeHome = tapioca.app.router.reverse('collectionHome');
		var routeEdit = tapioca.app.router.reverse('collectionEdit');

		var breadcrumb = [
			{
				url: 'app',
				name: app.name,
				is_active: false
			},
			{
				url: tapioca.app.router.createUri(routeHome, [appslug, namespace]),
				name: model.get('name'),
				is_active: false
			},
			{
				url: tapioca.app.router.createUri(routeEdit, [appslug, namespace]),
				name: 'Edit', // TODO: i18n
				is_active: true
			}
		];

		Subnav.Breadcrumb(breadcrumb);
	});


	mediator.subscribe('callCollectionAdd', function(appslug)
	{
		var app   = tapioca.apps[appslug];
		var route = tapioca.app.router.reverse('collectionAdd');
		
		var breadcrumb = [
			{
				url: 'app',
				name: app.name,
				is_active: false
			},
			{
				url: tapioca.app.router.createUri(route, [appslug]),
				name: 'Add', // TODO: i18n
				is_active: true
			}			
		];

		Subnav.Breadcrumb(breadcrumb);
	});

	// Required, return the module for AMD compliance
	return Subnav;

});