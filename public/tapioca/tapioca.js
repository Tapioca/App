define([
	'config',
	'underscore',
	'backbone'
], function(config)
{

	var tapioca = {
		
		// User config
		config:{},

		// Apps state
		apps: {},
		
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
