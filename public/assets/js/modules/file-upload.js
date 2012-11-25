
$.Tapioca.Components.FileUpload = {

    init: function(_settings, _success, _error)
    {
        var defaults = {
            autoUpload:             true,
            singleFileUploads:      false,
            limitMultiFileUploads:  5,
            limitConcurrentUploads: 1,
            formData:               {},
            height:                 300
        };

        var tpl    = Handlebars.compile( $.Tapioca.Tpl.components.upload ),
            config = $.extend(defaults, _settings || {}),
            flag   = false,
            $fileupload,
            $tags,
            $start,
            $list,
            $dialog,
            uploader,
            uploaderData;

        // accept file types based on app settings
        extwhitelist = $.Tapioca.UserApps[ config.appslug ].app.get('library.extwhitelist');

        config.acceptFileTypes = "/(\.|\/)(" + extwhitelist.join('|') + ")$/i";


        $.Tapioca.Dialog.open({
            height: config.height
        });

        $dialog = $('#dialog-modal');

        $dialog.html( tpl({
            appslug: config.appslug,
            multiple: (!config.singleFileUploads),
            filename: config.filename || false,
        }))

        $fileupload = $('#fileupload');
        $tags       = $('#tags');
        $list       = $('#upload-files-list');

        $('#fileupload-trigger').click($fileupload.click);
        $('#close-upload').click(function()
        {
            $.Tapioca.Components.FileUpload.clean();
            $.Tapioca.Dialog.close();
        });
        $('#fileinput-clear').click(function()
        {
            $dialog.find('div.alert').remove();
            $list.empty();
        })
        $('#btn-start-upload').click(function()
        {
            $dialog.find('div.alert').remove();

            if( flag )
            {
                uploaderData.submit();
            }
        })

        $fileupload.fileupload({
            dataType:               'json',
            autoUpload:             config.autoUpload,
            singleFileUploads:      config.singleFileUploads,
            limitMultiFileUploads:  config.limitMultiFileUploads,
            limitConcurrentUploads: config.limitConcurrentUploads,
            formData:               config.formData,
            acceptFileTypes:        config.acceptFileTypes,
            add: function (e, data) 
            {
                var valid  = true,
                    text   = '',
                    errors = [];

                uploaderData = data;

                $.each(data.files, function (index, file)
                {
                    if (!(uploader.options.acceptFileTypes.test(file.type) ||
                            uploader.options.acceptFileTypes.test(file.name))) 
                    {
                        valid = false;
                        errors[ index ] = 'Filetype not allowed';
                    }

                    if (uploader.options.maxFileSize &&
                            file.size > uploader.options.maxFileSize) 
                    {
                        valid = false;
                        errors[ index ] = 'File is too big';
                    }

                    if( errors.length > 0 ) 
                        text = errors.join(', ');

                    $list.append('<li class="float-fixer">' + file.name + text + '</li>');
                });

                if( valid )
                    flag = true;

                return;
            },
            progress: function (e, data)
            {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                console.log(progress)
            },
            done: function (e, data)
            {
                var refresh = false;

                if(typeof data.result[0].error != 'undefined')
                {
                    $list.before('<div class="alert">' + data.result[0].error + '</div>');
                }
                else
                {
                    // _.each(data.result, function( result )
                    // {
                    var $items = $list.find('li');

                    $.each(data.result, function (index, result)
                    {
                        var $item = $items.eq( index );

                        if(typeof result.error == 'undefined')
                        {
                            $item.append('<span class="pull-right label label-success">ok</span>');

                            refresh = true;

                            if( typeof _success == 'function')
                                _success(null, {
                                    filename: result.name,
                                    category: result.category
                                });
                        }
                        else
                        {
                            $item.append('<span class="pull-right label label-error">' + result.error + '</span>');
                        }
                    });
                }

                flag = false;

                if( refresh )
                {
                    var library = $.Tapioca.UserApps[ config.appslug ].library;
                        library.fetch();                    
                }
            },
            submit: function (e, data)
            {
                data.formData = {tags: $tags.val()};

                if (!data.formData.tags)
                {
                    $tags.focus();
                    return false;
                }
            }
        });

        uploader = $fileupload.data('fileupload');
    },

    clean: function()
    {
        $('#fileupload').fileupload('destroy');
    }
};
