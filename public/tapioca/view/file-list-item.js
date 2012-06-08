define([
	'hbs!template/content/file-list-item',
	'wtwui/Confirmation',
	'aura/mediator'
], function(tFileListItem, Confirmation, mediator)
{
	return Backbone.View.extend(
	{
		tagName: 'tr',

		template: tFileListItem,
		home: false,
		select: false,

		initialize: function(options)
		{
			this.home = (options.mode == 'home');
			this.select = (options.mode == 'select');

			this.model.bind('change', this.render, this);
			this.model.bind('destroy', this.close, this);
		},

		events: 
		{
			'click .delete-file-trigger': 'destroy',
			'click .select-file-trigger': 'selected'
		},

		selected: function(event)
		{
			var ret = {
				filename: this.model.get('filename'),
				category: this.model.get('category')
			}

			$('#app-content').trigger('document:addFile', ret);
		},

		destroy: function(event)
		{
			var $target  = $(event.target),
				filename = $target.attr('data-filename'),
				self     = this;

			new Confirmation(
			{
				title:'',
				message: 'Voulez vous effacer ce ficher ?',
				ok: function()
				{
					self.model.destroy();
				},
				cancel: function(){},
				overlay: {
					css: {
						background: 'black'
					}
				}
			})
			.show();

		},

		render: function(eventName)
		{
			var _html = tFileListItem({
							file: this.model.toJSON(),
							select: this.select,
							home: this.home
						});
			
			this.$el.html(_html);
			
			return this;
		},

		onClose: function()
		{
			this.model.unbind('change', this.render);
			this.model.unbind('destroy', this.close);
		}
	});
});