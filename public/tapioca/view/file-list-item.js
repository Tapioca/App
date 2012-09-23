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
		tag: true,
		category: true,

		initialize: function(options)
		{
			this.home = (options.mode == 'home');
			this.select = (options.mode == 'select');
			this.mergedTags = options.mergedTags;

			this.model.bind('change', this.render, this);
			this.model.bind('destroy', this.close, this);
		},

		events: 
		{
			'click .delete-file-trigger': 'destroy',
			'click .select-file-trigger': 'selected',
			'files::filterTags':          'filterTags',
			'files::filterCategory':      'filterCategory'
		},

		filterTags: function(event, tag)
		{
			if(tag == 'all')
			{
				this.$el.show();
				this.tag = true;
				return;
			}

			if(this.category)
			{
				if($.inArray(tag, this.mergedTags) == -1)
				{
					this.tag = false;
					this.$el.hide();
					return;
				}
				this.tag = true;
				this.$el.show();
			}
		},

		filterCategory: function(event, category)
		{
			if(category == 'all')
			{
				this.$el.show();
				this.category = true;
				return;
			}

			if(this.tag)
			{
				if(this.model.get('category') != category)
				{
					this.category = false;
					this.$el.hide();
					return;
				}

				this.category = true;
				this.$el.show();
			}
		},

		selected: function(event)
		{
			var ret = {
				filename: this.model.get('filename'),
				category: this.model.get('category')
			}

			$('#app-content').trigger('document:addFile', ret);
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
			var _html = tFileListItem({
							file: this.model.toJSON(),
							select: this.select,
							home: this.home,
							mergedTags: this.mergedTags
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