define([
	'tapioca',
	'view/content',
	'text!template/content/collection-edit.html',
	'linedtextarea'
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
				view.structure = JSON.stringify(view.structure, null, "    ");
				view.summary   = JSON.stringify(view.summary, null, "    ");
				view.callback  = JSON.stringify(view.callback, null, "    ");
				// view.rules     = JSON.stringify(view.rules);

			this.app_id = view.app_id;

			if(view.summaryEdit)
			{
				view.summaryEdit = ' checked="checked"';
			}

			var _html = Mustache.render(tContent, view);

			this.html(_html, 'app-form');
			

			$(".lined").linedtextarea();

			return this;
		},

		events:
		{
			'keyup :input': 'change',
			'click #tapioca-collection-form-save': 'save',
			'click button[type="reset"]': 'cancel'
			// 'click .save'  : 'save',
			// 'click .delete': 'delete'
		},

		change: function(event)
		{
			tapioca.beforeunload = {
				type: 'confirm',
				title: 'Etes vous sur de vouloir quiter cette page ? ',
				message: 'Vos modifications ne seront pas sauvegarder'
			};

			// var target = event.target;
			// console.log('changing ' + target.id + ' from: ' + target.defaultValue + ' to: ' + target.value);
			// You could change your model on the spot, like this:
			// var change = {};
			// change[target.name] = target.value;
			// this.model.set(change);

			$('#tapioca-collection-form-save').removeClass('disabled').removeAttr('disabled');
		},

		save: function()
		{
			// var slug = $('#app_id').val();
			tapioca.beforeunload = false;

			this.model.set(
			{
				name       : $('#name').val(),
				desc       : $('#desc').val(),
				status     : $('#status').val(),
				structure  : jQuery.parseJSON($('#structure').val()),
				summary    : jQuery.parseJSON($('#summary').val()),
				summaryEdit: $('#summary-edit').is(':checked'),
				callback   : jQuery.parseJSON($('#callback').val()),
				// rules     : jQuery.parseJSON($('#rules').val())
			});

			if (this.model.isNew())
			{
				tapioca.apps[ this.app_id ].models.create(this.model);
			}
			else
			{
				this.model.save();
			}
			return false;
		},

		cancel: function()
		{
			tapioca.beforeunload = false;
			window.history.back();
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
			this.model.unbind('change', this.render);
		}
	});

	return view;
});