define([
	'hbs!template/content/document-list-item'
], function(tDocumentListItem)
{
	return Backbone.View.extend(
	{
		tagName: 'tr',

		template: tDocumentListItem,
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
			'click .delete-doc-trigger': 'destroy',
			'click .select-doc-trigger': 'selected'
		},

		selected: function(event)
		{
			var ret = this.model.toJSON();

			$('#app-content').trigger('document:addDoc', ret);
			$('#ref-popin-content').trigger('popin:close');
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
			var _html = tDocumentListItem({
							doc: this.model.toJSON(),
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