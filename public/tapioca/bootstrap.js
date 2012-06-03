require.config(
{
	'paths': 
	{
		'text'             : '../assets/library/require/text',
		'order'            : '../assets/library/require/order',
		'hbs'              : '../assets/library/require/hbs',
		'jquery'           : '../assets/library/jquery/jquery-1.7.2',
		'underscore'       : '../assets/library/underscore/underscore',
		'underscore.string': '../assets/library/underscore/underscore.string',
		'backbone'         : '../assets/library/backbone/backbone',
		'Mustache'         : '../assets/library/mustache/mustache-wrap',
		'nanoScroller'     : '../assets/library/nanoscroller/jquery.nanoscroller',
		'Handlebars'       : '../assets/library/handlebar/Handlebars',
		'moment'           : '../assets/library/moment/moment-wrap',
		'form2js'          : '../assets/library/form2js/form2js-wrap',
		'bootbox'          : '../assets/library/bootstrap/bootbox.amd'
	},
	// default plugin settings, listing here just as a reference
	hbs : 
	{
		templateExtension : 'html',
		// if disableI18n is `true` it won't load locales and the i18n helper
		// won't work as well.
		disableI18n : false
	}
});
 
require([
	'order!jquery',
	'order!nanoScroller',
	'bootbox', 
	'tapioca',
	'aura/mediator',
	'view/apps-list',
	'module/breadcrumb',
	'module/collection',
	'module/document',
	'module/list'
], function($, nanoScroller, bootbox, tapioca, mediator, vAppCollections, Breadcrumb, Collections, Document, List)
{
	// Defining the application router.
	var Router = Backbone.Router.extend(
	{
		instance: false,
		
		initialize: function()
		{
			var total  = tapioca.config.user.groups.length;
			var loaded = 0;
			var loader = null;
			var self   = this;
			var check  = function()
						{
							if(loaded == total)
							{
								window.clearInterval(loader);
								self.instance = true;
								if(self.requestedFnc)
									self.onRequest(self.requestedFnc, self.requestedArgs);
							}
						};

			// Load Collections per Groups
			for(var i in tapioca.config.user.groups)
			{
				var slug = tapioca.config.user.groups[i].slug;

				// Create an instance for the group
				tapioca.apps[slug] = {};

				tapioca.apps[slug].name         = tapioca.config.user.groups[i].name;
				tapioca.apps[slug].documents    = {};
				tapioca.apps[slug].models       = new Collections.Collection(slug);
				tapioca.apps[slug].models.model = Collections.Model;

				new vAppCollections({
					el: $('#app-nav-collections-'+slug),
					model: tapioca.apps[slug].models, 
					appSlug: slug
				});

				tapioca.apps[slug].models.fetch({
					success: function()
					{
						++loaded;
					},
					error: function()
					{
						++loaded;
					}
				});
			}

			loader = window.setInterval(check,100);

		},

		routes: {
			''                                               : 'index',
			'app'                                            : 'index',
			'app/:appslug/collections/new'                   : 'collectionNew',
			'app/:appslug/collections/:namespace/edit'       : 'collectionEdit',
			'app/:appslug/collections/:namespace'            : 'collectionHome',
			'app/:appslug/document/:namespace/new'           : 'documentNew',
			'app/:appslug/document/:namespace/:ref/:revision': 'documentRef',
			'app/:appslug/document/:namespace/:ref'          : 'documentRef',
			'*path'                                          : 'notFound'
		},

		notFound: function(path) {
			var msg = "Unable to find path: " + path;
			console.log(msg);
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
			if(this.instance)
			{
				mediator.publish('callCollectionHome', appslug, namespace);
			}
			else
			{
				this.requestedFnc  = 'collectionHome';
				this.requestedArgs = [appslug, namespace];
			}
		},

		collectionEdit: function(appslug, namespace)
		{
			if(this.instance)
			{
				var model = tapioca.apps[appslug].models.get(namespace);
				model.fetch();
			
				mediator.publish('callCollectionEdit', appslug, namespace);
			}
			else
			{
				this.requestedFnc  = 'collectionEdit';
				this.requestedArgs = [appslug, namespace];
			}
		},

		collectionNew: function(appslug)
		{
			if(this.instance)
			{
				mediator.publish('callCollectionNew', appslug);
			}
			else
			{
				this.requestedFnc  = 'collectionAdd';
				this.requestedArgs = [appslug];
			}
		},

		documentRef: function(appslug, namespace, ref, revision)
		{
			if(this.instance)
			{
				mediator.publish('callDocumentRef', appslug, namespace, ref, revision);
			}
			else
			{
				this.requestedFnc  = 'documentRef';
				this.requestedArgs = [appslug, namespace, ref, revision];
			}
		},

		documentNew: function(appslug, namespace)
		{
			if(this.instance)
			{
				mediator.publish('callDocumentNew', appslug, namespace);
			}
			else
			{
				this.requestedFnc  = 'documentNew';
				this.requestedArgs = [appslug, namespace];
			}
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
		var $navPane      = $sidebar.find('pane-content');
		var $navApps      = $sidebar.find('div.app-nav');
		var $navAppActive = $sidebar.find('div.app-nav.app-nav-active');

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
					$parent
						.addClass('app-nav-active');
						/*
						.bind(tapioca.transitionEnd, function()
						{
							console.log($parent.offset().top)
							$navPane.scrollTop($parent.offset().top);
							$(this).unbind(tapioca.transitionEnd);
						});
						*/
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

			if(!tapioca.beforeunload)
			{
				// `Backbone.history.navigate` is sufficient for all Routers and will
				// trigger the correct events.  The Router's internal `navigate` method
				// calls this anyways.
				Backbone.history.navigate(href, true);
			}
			else
			{
				bootbox.confirm(tapioca.beforeunload.message, function(result)
				{
					tapioca.beforeunload = false;
					if (result)
					{
						Backbone.history.navigate(href, true);
					}
				});
			}
		}
	});
});