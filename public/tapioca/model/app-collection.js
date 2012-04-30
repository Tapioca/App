define([
	'backbone'
], function(Backbone)
{
	var model = Backbone.Model.extend(
	{
		urlRoot: '/api',
		url: function()
		{
			return this.urlRoot + '/' + this.get('app_id') + '/collection' + '/' + this.get('namespace');
		},
		defaults:{
			'name': '',
			'namespace': null,
			'app_id': null,
			'desc': '',
			'status': 1,
			'structure': '',
			'summary': ''
		},
		idAttribute: 'namespace'
	});

	return model;
});