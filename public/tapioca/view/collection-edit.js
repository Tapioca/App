define([
	'tapioca',
	'view/content',
	'text!template/content/collection-edit.html',
], function(tapioca, vContent, tContent)
{
	var view = vContent.extend(
	{
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
				view.callback  = JSON.stringify(view.callback);
				view.rules     = JSON.stringify(view.rules);

			var _html = Mustache.render(tContent, view);

			this.html(_html);
			
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
				name      : $('#name').val(),
				desc      : $('#desc').val(),
				status    : $('#status').val(),
				structure : jQuery.parseJSON($('#structure').val()),
				summary   : jQuery.parseJSON($('#summary').val()),
				callback  : jQuery.parseJSON($('#callback').val()),
				rules     : jQuery.parseJSON($('#rules').val())
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