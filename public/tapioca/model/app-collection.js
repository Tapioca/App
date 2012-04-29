define([
	'backbone'
], function(Backbone)
{
	return Backbone.Model.extend(
	{
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.get('namespace') + '/collection';
		},
		defaults:{
			'name': '',
			'namespace': null,
			'desc': '',
			'status': 1,
			'structure': '',
			'summary': ''
		},
		idAttribute: 'namespace'
	})
});