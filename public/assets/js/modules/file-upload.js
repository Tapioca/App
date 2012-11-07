
$.Tapioca.Components.FileUpload = {

    init: function(_options, _success, _error)
    {
        var options   = _options || {},
            _formData = options.formData || {},
            tpl       = Handlebars.compile( $.Tapioca.Tpl.components.upload ),
            $fileupload;

        $.Tapioca.Dialog.open({
            height: 300
        });

        $('#dialog-modal').html( tpl({
            appslug: options.appslug
        }))

        $fileupload = $('#fileupload');

        $('#fileupload-trigger').click(function()
        {
            $fileupload.click()
        })

        $fileupload.fileupload({
            dataType:               'json',
            singleFileUploads:      true,
            limitMultiFileUploads:  1,
            limitConcurrentUploads: 1,
            formData:               _formData,
            done: function (e, data)
            {
                if(typeof data.result[0].errors == 'undefined')
                {
                    var library = $.Tapioca.UserApps[ options.appslug ].library;
                        library.fetch();

                    console.log(data.result);
                }
                else
                {
                    alert(data.result[0].errors);
                }
            }
        });


        $fileupload.bind('fileuploadsubmit', function (e, data)
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
    },

    clean: function()
    {
        $('#fileupload').fileupload('destroy');
    }
};
