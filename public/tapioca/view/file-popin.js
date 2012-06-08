define([
	'Handlebars',
	'view/content',
	'view/file-list',
	'hbs!template/content/file-list'
], function(Handlebars, vContent, vFileList, tContent)
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

		render: function()
		{
			var _html = tContent({}),
				self  = this;

			this.$el.html(_html);
			
			return this;
		},

		onClose: function()
		{
			this.fileList.close();
		}
	});

	return view;
});