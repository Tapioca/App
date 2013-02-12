
$.Tapioca.Views.CollectionEdit = $.Tapioca.Views.FormView.extend(
{
    initialize: function( options )
    {
        this.isNew = options.isNew;

        this.$el.appendTo('#app-content');

        this.tplPreview = Handlebars.compile( $.Tapioca.Tpl.app.container.collection['preview-edit'] );

        this.model.bind('reset',  this.render, this);
        this.model.bind('change', this.displayJson, this);

        return this;
    },

    events: _.extend({
        'keyup #namespace' : 'slugify',
        'keyup #name'      : 'slugify'
    }, $.Tapioca.Views.FormView.prototype.events),


    slugify: function( event )
    {
        if( this.isNew && event.target.value != '')
        {
            this.$namespace.val( $.Tapioca.Components.Form.slugify( event.target.value ) );
        }
    },


    addRepeatNode: function()
    {
        this.$el.find('ul.input-repeat-list').append( this.tplPreview({}));
    },

    render: function()
    {
        var model       = this.model.toJSON();

        // if( !this.isNew )
        // {
        //     model.schema        = JSON.stringify( model.schema,        null, ' ');
        //     model.digest.fields = JSON.stringify( model.digest.fields, null, ' ');
        //     model.hooks         = JSON.stringify( model.hooks,         null, ' ');            
        // }

        model.isNew     = this.isNew;
        model.pageTitle = ( this.isNew ) ?
                            __('title.new_collection') :
                            $.Tapioca.I18n.get('title.edit_collection', this.model.get('name'));

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.edit ),
            html = tpl( model );

        this.html( html, 'app-form');

        this.$namespace = $('#namespace');

        //this.editor();
        // this.$el.find('textarea.lined').linedtextarea();

        this.displayJson()

        return this;
    },

    displayJson: function()
    {
        var model = this.model.toJSON();

        $('#schema').val( JSON.stringify( model.schema,        null, ' ') );
        $('#digest').val( JSON.stringify( model.digest.fields, null, ' ') );
        $('#hooks').val(  JSON.stringify( model.hooks,         null, ' ') );
    },

    editor: function()
    {

    },

    submit: function()
    {
        this.$btnSubmit.button('loading');

        var previews = [];

        $('#collection-preview').find('input[name="preview-url"]').each(function( index )
        {
            var $this    = $(this),
                $parent  = $this.parents('li'),
                _label   = $parent.find('input[name="preview-label"]').val(),
                _url     = $this.val();

            if( !_.isBlank( _label ) && !_.isBlank( _url ) )
            {
                var _obj = {
                    label: _label,
                    url:   _url
                };

                previews.push( _obj )
            }
        });

        var self            = this,
            appslug         = $.Tapioca.appslug
            collectionModel = {
                name:      $('#name').val(),
                desc:      $('#desc').val(),
                status:    $('#status').val(),
                schema:    jQuery.parseJSON( $('#schema').val() ),
                digest:
                {
                    fields: jQuery.parseJSON( $('#digest').val() ),
                    edited: $('#digest-edit').is(':checked')
                },
                hooks : jQuery.parseJSON( $('#hooks').val() ),
                preview:   previews,
            };

        if( this.isNew )
        {
            collectionModel['namespace-suggest'] = $('#namespace').val();
            
            $.Tapioca.UserApps[ appslug ].collections.add( this.model );
        } 

        this.model.save( collectionModel, {
                success:function (model, response)
                {
                    self.namespace = model.get('namespace');

                    if( self.isNew )
                    {                        
                        var href = $.Tapioca.app.setRoute('appCollectionEdit', [ appslug, self.namespace ] )

                        Backbone.history.navigate( href );

                        var _namespace = model.get('namespace'),
                            _abstracts = new $.Tapioca.Collections.Abstracts({
                                appslug:   appslug,
                                namespace: self.namespace
                            });

                        $.Tapioca.UserApps[ appslug ].data[ self.namespace ] = {
                            schema:    false, 
                            abstracts: _abstracts
                        };
                    }

                    self.resetForm();
                },
                error: function(model, response)
                {
                    console.log( response)

                    self.resetForm();
                }
            });
    },

    onClose: function()
    {
        // $('#form').off();
        this.model.unbind('reset',  this.render);
        this.model.unbind('change', this.displayJson);
    }
});