define([
	'config',
	'underscore',
	'backbone'
], function(config, _, Backbone)
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
