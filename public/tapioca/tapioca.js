define([
	'order!jquery',
	'config',
	'underscore',
	'backbone'
], function($, config, _, Backbone)
{
	Backbone.emulateJSON = true;

	Backbone.View.prototype.close = function()
	{
		this.$el.empty();
		this.unbind();
		if (this.onClose)
		{
			this.onClose();
		}
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
