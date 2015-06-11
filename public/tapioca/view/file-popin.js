define([
	'Handlebars',
	'view/content',
	'view/file-list',
	'hbs!template/content/file-list',
	'wtwui/Overlay'
], function(Handlebars, vContent, vFileList, tContent, Overlay)
{
	var view = vContent.extend(
	{
		fileList: null,
		basePath: null,

		initialize: function(options)
		{

			this.render();
			
			this.fileList = new vFileList({
								el: $('#table-file-list').find('tbody'),
								collection: this.collection,
								mode: 'select'
							});
		},

		events: {
			'popin:close' : 'hide'
		},

		render: function()
		{
			var _html = tContent({}),
				self  = this;

			this.overlay = new Overlay();

			this.$el.find('div.pane-content').html(_html);

			this.overlay.show();
			
			this.$el.nanoScroller({
					paneClass: 'track',
					contentClass: '.pane-content'
				});

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