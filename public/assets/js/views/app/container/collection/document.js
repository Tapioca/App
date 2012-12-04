
$.Tapioca.Views.Document = $.Tapioca.Views.FormView.extend(
{
    loaded:   0,
    total:    4,

    initialize: function( options )
    {
        this.$el.appendTo('#app-content');

        this.appslug   = options.appslug;
        this.namespace = options.namespace;
        this.ref       = options.ref;
        this.revision  = options.revision;
        this.locale    = options.locale,

        this.isNew     = options.isNew;
        this.baseUri   = options.baseUri;
        
        this.schema    = options.schema;
        this.abstracts = options.abstracts;
        // this.doc       = options.doc;
        this.users     = options.users;

        // is that bad???
        // `this.model` is required for `change()`
        // but `this.doc` is cleaner as variable name
        this.model = this.doc = options.doc;

        // check if every needed ressources
        // are loaded
        if( !this.isNew )
            this.doc.fetch({ 
                data: options.docOptions,
                success: _.bind( this.ressourcesLoaded, this )
            });
        else
            ++this.loaded;

        if( !this.users.isFetched() )
            this.users.reload( _.bind( this.ressourcesLoaded, this ) );
        else
            ++this.loaded;

        if( this.schema.hasSchema() )
            this.schema.fetch({
                success: _.bind( this.ressourcesLoaded, this )
            });
        else
            ++this.loaded;

        if( !this.abstracts.isFetched() )
            this.abstracts.fetch({
                success: _.bind( this.ressourcesLoaded, this )
            });
        else
            ++this.loaded;


        this.isRessourcesLoaded();
        

        return this;
    },

    events: _.extend({
        'click #revisions a.revision-btn': 'loadRevision',
        'click button.btn-preview'       : 'getPreview'
    }, $.Tapioca.Views.FormView.prototype.events),

    isRessourcesLoaded: function()
    {
        if( this.loaded == this.total )
        {
            // current document abstatract
            this.abstract = ( this.isNew ) ? new $.Tapioca.Models.Abstract() : this.abstracts.get( this.ref );

            this.render();
            this.renderRev();
            this.renderDoc();
            this.setPreviewBtn();
        }

        return this;
    },

    ressourcesLoaded: function()
    {
        ++this.loaded;

        this.isRessourcesLoaded();

        return this;
    },

    getPageTitle: function()
    {
        return ( this.isNew ) ? __('title.new_document') : __('title.edit_document');
    },

    render: function()
    {
        var pageTitle = this.getPageTitle();

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.document ),
            html = tpl({
                appslug:    this.appslug,
                locale:     this.locale,
                baseUri:    this.baseUri,
                isNew:      this.isNew,
                pageTitle:  pageTitle
            });

        this.html( html, 'app-form');

        this.form = document.getElementById('tapioca-document-form');

        return this;
    },

    renderRev: function()
    {
        this.vRevisions = new $.Tapioca.Views.Revisions({
            model:    this.abstract,
            isNew:    this.isNew,
            appslug:  this.appslug,
            baseUri:  this.baseUri,
            locale:   this.locale,
            revision: this.revision,
            users:    this.users
        }).render();

    },

    renderDoc: function()
    {
        if( this.vDocument )
            this.vDocument.close();
        
        this.vDocument = new $.Tapioca.Views.DocForm({
            model:   this.doc,
            schema:  this.schema,
            appslug: this.appslug,
            baseUri: this.baseUri,
            locale:  this.locale,
            parent:  this
        });
    },

    loadRevision: function( event )
    {
        var $target = $( event.target ).parents('li');

        var fetchOptions = $.param( {r: $target.attr('data-revision') } );

        this.doc.fetch({ 
            data:   fetchOptions,
            success: _.bind( this.renderDoc, this )
        });
    },


    setPreviewBtn: function()
    {
        var previews = this.schema.get('preview');

        if( previews.length )
        {
            var $btn = $('#app-content').find('button.btn-preview');

            $btn.append('<span class="caret"></span>');
            $btn.dropdown()
        }
    },


    getPreview: function()
    {
        if( this.validateForm() )
        {
            var formData = form2js('tapioca-document-form', '.'),
                _url     = $.Tapioca.config.apiUrl + this.appslug + '/preview/' +  this.namespace;

            var put = $.ajax({
                url:      _url,
                data:     JSON.stringify( formData ),
                dataType: 'json',
                type:     'POST'
            });

            put.done( function( p )
            {
                var url      = $.Tapioca.config.previewUrl.replace(/{{previewId}}/, p._id),
                    tpl      = $.Tapioca.Tpl.app.container.collection.preview.replace(/{{url}}/g, url),
                    $overlay = $( tpl ).hide().appendTo('body');

                $overlay.fadeIn();

                $('#close-preview').click(function()
                {
                    $overlay.remove();
                })
            });

            put.fail( this.error );
        }
    },

    submit: function()
    {
        if( this.validateForm() )
        {
            var formData = form2js('tapioca-document-form', '.'),
                self     = this,
                isNew    = this.model.isNew();

            // Sets button state to loading - disables button and swaps text to loading text
            this.$btnSubmit.button('loading');

            this.model.save(formData, {
                success:function (model, response)
                {
                    // prevent this.change()  to be trigged on render
                    // self.vDocument.initialized = false;
                    self.ref = model.get('_ref');

                    if(isNew)
                    {
                        self.abstract.set({_ref: self.ref});

                        self.abstracts.add( self.abstract );

                        var href = $.Tapioca.app.setRoute('appCollectionRef', [ self.appslug, self.namespace, self.ref ] )

                        Backbone.history.navigate( href );
                    }

                    self.abstract.fetch();
                    self.resetForm();
                },
                error: function(model, response)
                {
                    // self.model.set( self.model.previousAttributes() );
                    console.log( response)

                    self.resetForm();
                }
            });
        }

        return false;
    },

    onClose: function()
    {
        if( this.vRevisions )
            this.vRevisions.close();

        if( this.vDocument )
            this.vDocument.close();
    }

});