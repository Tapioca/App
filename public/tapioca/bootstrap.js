require.config({
	'paths': {
		'text': '../assets/library/require/text',
		'order': '../assets/library/require/order',
		'jquery': '../assets/library/jquery/jquery-1.7.2',
		'underscore': '../assets/library/underscore/underscore',
		'backbone': '../assets/library/backbone/backbone',
		'Mustache': '../assets/library/mustache/mustache-wrap',
		'nanoScroller': '../assets/library/nanoscroller/jquery.nanoscroller'
	}
});
 
require([
	'order!jquery',
	'order!nanoScroller', 
	'tapioca',
	'aura/mediator',
	'view/apps-list',
	'module/collection'
], function($, nanoScroller, tapioca, mediator, vAppCollections, Collections)
{
	// Defining the application router, you can attach sub routers here.
	var Router = Backbone.Router.extend(
	{
		instance: false,
		
		initialize: function()
		{
			// Load Collections per Groups
			for(var i in tapioca.config.user.groups)
			{
				var slug = tapioca.config.user.groups[i].slug;

				// Create an instance for the group
				tapioca.apps[slug] = {};

				tapioca.apps[slug].models = new Collections.Collection(slug);
				tapioca.apps[slug].models.model = Collections.Model;

				new vAppCollections({
					el: $('#app-nav-collections-'+slug),
					model: tapioca.apps[slug].models, 
					appSlug: slug
				});

				tapioca.apps[slug].models.fetch();
			}
		},

		routes: {
			''                                        : 'index',
			'app/:appslug/collections/add'            : 'collectionAdd',
			'app/:appslug/collections/:namespace/edit': 'collectionEdit',
			'app/:appslug/collections/:namespace'     : 'collectionHome'
		},

		onRequest: function(fnc, args)
		{
			this[fnc].apply(this, args || []);
		},

		index: function()
		{
			console.log('router index')
		},

		collectionHome: function(appslug, namespace)
		{
			mediator.publish('callCollectionHome', appslug, namespace);
		},

		collectionEdit: function(appslug, namespace)
		{
			var model = tapioca.apps[appslug].models.get(namespace);
			model.fetch();
		
			mediator.publish('callCollectionEdit', appslug, namespace);
		},

		collectionAdd: function(appslug)
		{
			mediator.publish('callCollectionAdd', appslug);
		}

	});

	// Shorthand the application namespace
	var app = tapioca.app;

	$(function()
	{
		var $sidebar = $('#apps-nav');
		var _options = {
			paneClass: 'track',
			contentClass: '.pane-content'
		};

		//$sidebar.nanoScroller(_options);
		$('#main').find('div.nano').nanoScroller(_options);

		// Sidebar
		var $navApps      = $sidebar.find('div.app-nav');
		var $navAppActive = $sidebar.find('div.app-nav.app-nav-active');
		var $navLinks     = $sidebar.find('div.app-nav-lists a');

		$navLinks.click(function(event)
		{
			event.preventDefault();

			$navLinks.removeClass('active');
			$(this).addClass('active');
		})

		$sidebar.find('a.app-nav-header').click(function(event)
		{
			event.preventDefault();

			var $parent = $(this).parent('div.app-nav');

			if(!$parent.hasClass('app-nav-active'))
			{
				$navAppActive.find('div.app-nav-lists').slideUp(200, function()
				{
					$navApps.removeClass('app-nav-active');
					//$parent.addClass('app-nav-active');
				});
				
				$navAppActive = $parent;

				$parent.find('div.app-nav-lists').slideDown(200, function()
				{
					$parent.addClass('app-nav-active');
				});
			}
		});

		// Define master router on the application namespace and trigger all
		// navigation from this instance.		
		app.router = new Router();

		// Trigger the initial route and enable HTML5 History API support
		Backbone.history.start({ pushState: true });
	});

	// All navigation that is relative should be passed through the navigate
	// method, to be processed by the router.  If the link has a data-bypass
	// attribute, bypass the delegation completely.
	$(document).on('click', 'a:not([data-bypass])', function(event)
	{
		// Get the anchor href and protcol
		var href = $(this).attr("href");
		var protocol = this.protocol + "//";

		// Ensure the protocol is not part of URL, meaning its relative.
		if (href && href.slice(0, protocol.length) !== protocol && href.indexOf("javascript:") !== 0) 
		{
			// Stop the default event to ensure the link will not cause a page
			// refresh.
			event.preventDefault();

			// `Backbone.history.navigate` is sufficient for all Routers and will
			// trigger the correct events.  The Router's internal `navigate` method
			// calls this anyways.
			Backbone.history.navigate(href, true);
		}
	});
});