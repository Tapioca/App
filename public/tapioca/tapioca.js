define([
	'order!jquery',
	'config',
	'underscore',
	'backbone'
], function($, config, _, Backbone)
{
	Backbone.emulateJSON = true;

	// Zombies! RUN!
	// Managing Page Transitions In Backbone Apps
	// http://lostechies.com/derickbailey/2011/09/15/zombies-run-managing-page-transitions-in-backbone-apps/
	Backbone.View.prototype.close = function()
	{
		this.$el.empty();
		this.unbind();
		if (this.onClose)
		{
			this.onClose();
		}
	}

	// FuelPhp like reversed route,
	// give method and it return the associated route
	Backbone.Router.prototype.reverse = function(_method)
	{
		for(var path in this.routes)
		{
			if(this.routes[path] == _method)
			{
				return path;
			}
		}
		return false;
	}

	Backbone.Router.prototype.createUri = function(_route, _hash)
	{
		var regexp  = new RegExp( /:\w+/g );
		var replace = _route.match( regexp );

		for(var i = -1; ++i< _hash.length;)
		{  
			_route = _route.replace(replace[i], _hash[i]);
		}

		return _route;
	}


	var vP = '',
		transitionEnd = 'transitionEnd';

	if ($.browser.webkit) {
		vP = "-webkit-";
		transitionEnd = "webkitTransitionEnd";
	} else if ($.browser.msie) {
		vP = "-ms-";
		transitionEnd = "msTransitionEnd";	
	} else if ($.browser.mozilla) {
		vP = "-moz-";
		transitionEnd = "transitionend";
	} else if ($.browser.opera) {
		vP = "-o-";
		transitionEnd = "oTransitionEnd";
	}

	var tapioca = {
		
		// User config
		config:{},

		vendor: vP,

		transitionEnd: transitionEnd,
		
		// Apps state
		apps: {},

		view: null,
		
		// Create a custom object with a nested Views object
		module: function(additionalProps)
		{
		  return _.extend({ Views: {} }, additionalProps);
		},

		// Keep active application instances namespaced under an app object.
		app: _.extend({}, Backbone.Events)
	};

	_.extend(tapioca.config, config);
	
	return tapioca;

});
