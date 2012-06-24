define([
	'tapioca',
	'Handlebars',
	'view/content',
	'hbs!template/content/file-upload',
	'fileupload',
	'fileupload-ui'
], function(tapioca, Handlebars, vContent, tContent, fileupload, fileuploadUi)
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
		},

		render: function()
		{
			var _html = tContent({
					appSlug: this.appSlug,
					rootUri: tapioca.config.root_uri
				}),
				self  = this;

			this.html(_html);

			var locale = {
				"fileupload": 
				{
					"errors": {
						"maxFileSize": "File is too big",
						"minFileSize": "File is too small",
						"acceptFileTypes": "Filetype not allowed",
						"maxNumberOfFiles": "Max number of files exceeded",
						"uploadedBytes": "Uploaded bytes exceed file size",
						"emptyResult": "Empty file upload result"
					},
					"error": "Error",
					"start": "Start",
					"cancel": "Cancel",
					"destroy": "Delete"
				}
			};

			$('#fileupload').fileupload({
				dataType: 'json',

/*
				add: function (e, data) 
				{
					$('#upload-tags').add('button.start').fadeIn();
				},

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
				},
*/
				uploadTemplateId: null,
				downloadTemplateId: null,
				uploadTemplate: function (o) {
				    var rows = $();
				    $.each(o.files, function (index, file) {
				        var row = $('<tr class="template-upload fade">' +
				            '<td class="preview"><span class="fade"></span></td>' +
				            '<td class="name"></td>' +
				            '<td class="size"></td>' +
				            (file.error ? '<td class="error" colspan="2"></td>' :
				                    '<td><div class="progress">' +
				                        '<div class="bar" style="width:0%;"></div></div></td>' +
				                        '<td class="start"><button class="btn btn-primary"><i class="icon-upload icon-white"></i> Start</button></td>'
				            ) + '<td class="cancel"><button class="btn btn-warning"><i class="icon-ban-circle icon-white"></i> Cancel</button></td></tr>');
				        row.find('.name').text(file.name);
				        row.find('.size').text(o.formatFileSize(file.size));
				        if (file.error) {
				            row.find('.error').text(
				                locale.fileupload.errors[file.error] || file.error
				            );
				        }
				        rows = rows.add(row);
				    });
				    return rows;
				},
				downloadTemplate: function (o) {
				    var rows = $();
				    $.each(o.files, function (index, file) {
				        var row = $('<tr class="template-download fade">' +
				            (file.error ? '<td></td><td class="name"></td>' +
				                '<td class="size"></td><td class="error" colspan="2"></td>' :
				                    '<td class="preview"></td>' +
				                        '<td class="name"><a></a></td>' +
				                        '<td class="size"></td><td colspan="2"></td>'
				            ) + '<td class="delete"><button class="btn btn-danger"><i class="icon-trash icon-white"></i> Delete</button> ' +
				                '<input type="checkbox" name="delete" value="1"></td></tr>');
				        row.find('.size').text(o.formatFileSize(file.size));
				        if (file.error) {
				            row.find('.name').text(file.name);
				            row.find('.error').text(
				                locale.fileupload.errors[file.error] || file.error
				            );
				        } else {
				            row.find('.name a').text(file.name);
				            if (file.thumbnail_url) {
				                row.find('.preview').append('<a><img></a>')
				                    .find('img').prop('src', file.thumbnail_url);
				                row.find('a').prop('rel', 'gallery');
				            }
				            row.find('a').prop('href', file.url);
				            row.find('.delete button')
				                .attr('data-type', file.delete_type)
				                .attr('data-url', file.delete_url);
				        }
				        rows = rows.add(row);
				    });
				    return rows;
				}
			});

			$('#fileupload').bind('fileuploadsubmit', function (e, data)
			{
				// The example input, doesn't have to be part of the upload form:
				var input = $('#tags');
				data.formData = {tags: input.val()};

				if (!data.formData.tags)
				{
					input.focus();
					return false;
				}
			});

			return this;
		}
	});

	return view;
});