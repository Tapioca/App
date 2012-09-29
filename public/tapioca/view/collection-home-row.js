define([
	'tapioca',
	'aura/mediator',
	'hbs!template/content/collection-home-row',
	'dropdown',
	'template/helpers/setStatus',
	'wtwui/Confirmation'
], function(tapioca, mediator, tContent, dropdown, setStatus, Confirmation)
{
	var view = Backbone.View.extend(
	{
		tagName: 'tr',

		initialize: function(options)
		{
			this.$parent = options.parent;

			this.$parent.append(this.$el);

			var data = {
				editable  : options.editable,
				namespace : options.namespace,
				appslug   : options.appslug,
				appId     : options.appId
			}

			this.data = $.extend({}, this.model.toJSON(), data);

			this.render();

			var self = this;

			mediator.subscribe('search::clear', function()
			{
				self.$el.show();
			});

			mediator.subscribe('search::send', function(pattern)
			{
				self.$el.show();

				var data  = self.model.get('data'),
					valid = false;

				for(var i in data)
				{
					if( pattern.test( data[i] ) )
						valid = true;
				};

				if( !valid )
					self.$el.hide();
			})
		},

		events: {
			'click .btn-delete-trigger': 'deleteDoc'
		},

		deleteDoc: function(event)
		{
			var self = this;

			new Confirmation(
			{
				title:'',
				message: 'Voulez vous effacer ce document ?',
				ok: function()
				{
					self.model.destroy();
					self.$el.remove();
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

		render: function()
		{
			var _html = tContent(this.data)

			this.$el.html(_html);

			this.$el.find('.dropdown-toggle').dropdown();
			this.$el.find('ul[data-type="set-status"] a').setStatus();

			return this;
		},

		onClose: function()
		{
		}
	});

	return view;
});