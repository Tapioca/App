define([
	'tapioca',
	'view/content',
	'view/file-list',
	'hbs!template/content/file-home'
], function(tapioca, vContent, vFileList, tContent)
{
	var view = vContent.extend(
	{
		fileList: null,
		basePath: null,

		initialize: function(options)
		{
			this.basePath = options.publicStorage;
			this.appSlug  = options.appSlug;

			this.render();
			
			this.fileList = new vFileList({
								el: $('#table-file-list').find('tbody'),
								collection: this.collection
							});
		},

		render: function()
		{
			var _html = tContent({
					appSlug: this.appSlug,
					rootUri: tapioca.config.root_uri
				}),
				self  = this;

			this.html(_html);

			
			return this;
		},

		onClose: function()
		{
			$('#app-nav-files-'+this.appSlug).find('li[data-namespace="library"]').removeClass('active');
			
			this.fileList.close();
		}
	});

	return view;
});