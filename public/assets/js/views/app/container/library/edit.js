
$.Tapioca.Views.EditFile = $.Tapioca.Views.FormView.extend(
{
	initialize: function( options )
	{
        this.appslug = options.appslug;
        this.tplTag  = Handlebars.compile( $.Tapioca.Tpl.components['tag-edit'] );

        this.model.bind('change:filename', this.updateFilename, this);
	},

    events: _.extend({
        'click ul.input-repeat-list li:last-child a.input-repeat-trigger'       : 'addInput',
        'click ul.input-repeat-list li:not(:last-child) a.input-repeat-trigger' : 'removeInput',
        'click a.upload-trigger'                                                : 'openUpload'
    }, $.Tapioca.Views.FormView.prototype.events),

    addInput: function(event)
    {
        this.$el.find('ul.input-repeat-list').append( this.tplTag({}) );
    },

    removeInput: function(event)
    {
        $(event.target).parents('li').remove();
    },

    openUpload: function()
    {
        $.Tapioca.FileUpload.init({
            appslug:           $.Tapioca.appslug,
            singleFileUploads: true,
            filename:          this.model.get('filename')
        }, _.bind( this.refresh, this ));
    },

	render: function()
	{
		var preview = false;

		if( this.model.get('category') == 'image')
		{
			var baseUri  = $.Tapioca.config.filesUrl + $.Tapioca.appslug + '/image/',
				filename = this.model.get('filename'),
                    _tmp = new Date();

			preview = {
				thumb:    baseUri +'preview-' + filename+'?'+_tmp.getTime(),
				original: baseUri + filename
			};
		}

		var tpl   = Handlebars.compile( $.Tapioca.Tpl.app.container.library.edit ),
            html  = tpl({
            			preview: preview,
            			file:    this.model.toJSON()
            		});

        this.html( html );

        return this;
	},

    updateFilename: function()
    {
        $('#filename').text( this.model.get('filename') );
    },

    refresh: function()
    {
        this.model.fetch({
                success: _.bind( this.render, this)
            })
    },

	submit: function()
	{
        this.$btnSubmit.button('loading');

		var $tags = this.$el.find('input[name="tag"]'),
			self  = this,
			data  = {
				basename: $('#basename').val()
			};

		if( $tags.eq(0).val() !== '')
		{
			data.tags = [];
		}
		else
		{
			console.log('missing tag');
			return;
		}

		$tags.each(function()
		{
			data.tags.push( $(this).val() );
		});

		this.model.set( data )

		this.model.save( {}, {
                success: function( model, response )
                {
                    var href = $.Tapioca.app.setRoute('appLibraryRef', [ self.appslug, response.basename, response.extension ] )

                    Backbone.history.navigate( href );

                    self.resetForm();
                },
                error: function(model, response)
                {
                    console.log( response)

                    self.resetForm();
                }
            } )
	},

    onClose: function()
    {
        this.model.unbind('change', this.render);
    }
})