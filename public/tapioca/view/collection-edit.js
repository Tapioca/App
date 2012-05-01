define([
	'order!jquery',
	'order!nanoScroller',
	'backbone',
	'tapioca',
	'text!template/content/collection-edit.html',
], function($, nanoScroller, Backbone, tapioca, tContent)
{
	var view = Backbone.View.extend(
	{

		el: $('#app-content'),

		template: tContent,

		initialize: function()
		{
			this.model.bind('change', this.render, this);
			this.render();
		},

		render: function(eventName)
		{
			var view           = this.model.toJSON();
				view.structure = JSON.stringify(view.structure);
				view.summary   = JSON.stringify(view.summary);

			var _html = Mustache.render(tContent, view);

			this.$el
				.html(_html)
				.nanoScroller();
			
			return this;
		},

		events:
		{
			'change input': 'change',
			'click .save'  : 'save',
			'click .delete': 'delete'
		},

		change: function(event)
		{
			var target = event.target;
			console.log('changing ' + target.id + ' from: ' + target.defaultValue + ' to: ' + target.value);
			// You could change your model on the spot, like this:
			// var change = {};
			// change[target.name] = target.value;
			// this.model.set(change);
		},

		save: function()
		{
			var slug = $('#app_id').val();

			this.model.set(
			{
				name     : $('#name').val(),
				desc     : $('#desc').val(),
				status   : $('#status').val(),
				structure: jQuery.parseJSON($('#structure').val()),
				summary  : jQuery.parseJSON($('#summary').val())
			});

			if (this.model.isNew())
			{
				tapioca.apps[slug].models.create(this.model);
			}
			else
			{
				this.model.save();
			}
			return false;
		},

		delete: function()
		{
			this.model.destroy({
			success: function() {
			alert('Tapioca deleted successfully');
			window.history.back();
			}
			});
			return false;
		},

		onClose: function()
		{
			this.model.unbind('reset', this.render);
		}
	});

	return view;
});