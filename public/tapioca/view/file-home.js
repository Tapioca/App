define([
	'Handlebars',
	'view/content',
	'view/file-list',
	'hbs!template/content/file-home',
	'fileupload'
], function(Handlebars, vContent, vFileList, tContent, fileupload)
{
	var view = vContent.extend(
	{
		fileList: null,
		basePath: null,

		initialize: function(options)
		{
			this.basePath = options.publicStorage;

			this.render();
			
			this.fileList = new vFileList({
								el: $('#table-file-list').find('tbody'),
								collection: this.collection
							});
		},

		render: function()
		{
			var _html = tContent({}),
				self  = this;

			this.html(_html);

			$('#fileupload').fileupload({
				dataType: 'json',
/*
				add: function (e, data) 
				{
					$.each(data.files, function (index, file)
					{
						console.log('Added file: ' + file.name);
					});
				},
*/
				done: function (e, data)
				{
					console.log(data.result)
				},
				progress: function (e, data)
				{
					var progress = parseInt(data.loaded / data.total * 100, 10);
					console.log(progress)
				},
				always: function()
				{
					console.log('sync')
					Backbone.sync('read', self.collection, {
						success: function()
						{
							console.log('call render')
							self.fileList.render();
						}
					});
				}
			});
			
			return this;
		},

		onClose: function()
		{
			this.fileList.close();
		}
	});

	return view;
});