define([
	'Handlebars',
	'view/content',
	'view/document-list',
	'hbs!template/content/collection-popin',
	'wtwui/Overlay'
], function(Handlebars, vContent, vDocList, tContent, Overlay)
{
	var view = vContent.extend(
	{
		initialize: function(options)
		{
			this.header    = options.header;
			this.appslug   = options.appslug;
			this.namespace = options.namespace;

			this.render();

			this.fileList = new vDocList({
								el: $('#table-document-list').find('tbody'),
								collection: this.collection,
								mode: 'select'
							});
		},

		events: {
			'popin:close' : 'hide'
		},

		render: function()
		{
			this.header.thead = [];

			for(var i in this.header.summary)
			{
				this.header.thead.push(this.header.summary[i]);
			}
			
			var data = {
				header: this.header
			};

			var _html = tContent(data),
				self  = this;

			this.overlay = new Overlay();

			this.$el.find('div.pane-content').html(_html);

			this.$el.nanoScroller({
					paneClass: 'track',
					contentClass: '.pane-content'
				});

			this.overlay.show();

			$('#ref-popin')
				.addClass('active')
				.find('div.close')
					.click(function()
					{
						$('#ref-popin').removeClass('active');
						self.overlay.hide();			
					});
			
			return this;
		},

		hide: function()
		{
			$('#ref-popin').removeClass('active');
			this.overlay.hide();			
		},

		onClose: function()
		{
			this.fileList.close();
		}
	});

	return view;
});