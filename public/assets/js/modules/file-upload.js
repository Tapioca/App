
$.Tapioca.Components.FileUpload = {

    init: function(_settings, _success, _error)
    {
        var defaults = {
            autoUpload:             true,
            singleFileUploads:      true,
            limitMultiFileUploads:  1,
            limitConcurrentUploads: 1,
            formData:               {},
            height:                 300
        };

        var tpl    = Handlebars.compile( $.Tapioca.Tpl.components.upload ),
            config = $.extend({}, defaults, _settings || {}),
            $fileupload,
            $tags,
            $start,
            $list,
            uploader;

        // accept file types based on app settings
        extwhitelist = $.Tapioca.UserApps[ config.appslug ].app.get('extwhitelist');

        config.acceptFileTypes = "/(\.|\/)(" + extwhitelist.join('|') + ")$/i";


        $.Tapioca.Dialog.open({
            height: config.height
        });

        $('#dialog-modal').html( tpl({
            appslug: config.appslug,
            multiple: (!config.singleFileUploads)
        }))

        $fileupload = $('#fileupload');
        $tags       = $('#tags');
        $start      = $('#btn-start-upload');
        $list       = $('#upload-files-list');

        $('#fileupload-trigger').click($fileupload.click);
        $('#close-upload').click(function()
        {
            $.Tapioca.Components.FileUpload.clean();
            $.Tapioca.Dialog.close();
        });

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
                    text   = 'file added',
                    errors = [];

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

                    $list.append('<li>' + file.name + ': ' + text + '</li>');
                });

                $start.click(function()
                {
                    if( valid )
                        data.submit();
                });

                return;
            },
            progress: function (e, data)
            {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                console.log(progress)
            },
            done: function (e, data)
            {
                if(typeof data.result[0].errors == 'undefined')
                {
                    var library = $.Tapioca.UserApps[ config.appslug ].library;
                        library.fetch();

                    console.log(data.result);

                    if( typeof _success == 'function')
                        _success();
                }
                else
                {
                    alert(data.result[0].errors);
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
