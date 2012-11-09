
$.Tapioca.Views.Library = $.Tapioca.Views.Content.extend(
{
    initialize: function( options )
    {
        this.appslug   = $.Tapioca.appslug;
        this.tplRow    = Handlebars.compile( $.Tapioca.Tpl.app.container.library['index-row'] );
        this.tplTags   = Handlebars.compile( $.Tapioca.Tpl.components.tags );

        Handlebars.registerPartial('library-list',  $.Tapioca.Tpl.app.container.library['index-list'] );

        this.collection.bind('reset', this.display, this);

        this.$el.appendTo('#app-content');
    },

    events: {
        'click a.upload-trigger':  'upload',
        'click #tags-list li':     'filterTags',
        'click #category-list li': 'filterCategory'
    },

    render: function()
    {
        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.library.index ),
            html = tpl({
                appslug:    $.Tapioca.appslug,
                isAppAdmin: $.Tapioca.Session.isAdmin()
            });

        this.html( html );

        this.$table      = this.$el.find('tbody');
        this.$tagsList   = $('#tags-list');
        this.$categories = $('#category-list');

        if( this.collection.isFetched() )
        {
            this.display();
        }


        return this;
    },

    filterTags: function(event)
    {
        this.$tag.removeClass('active');

        var key = $(event.target).addClass('active').attr('data-tag');

        for( var i in this.viewpointer )
        {
            this.viewpointer[ i ].filterTags( key );
        }
    },

    filterCategory: function(event)
    {
        this.$category.removeClass('active');

        var key = $(event.target).addClass('active').attr('data-category');

        for( var i in this.viewpointer )
        {
            this.viewpointer[ i ].filterCategory( key );
        }
    },

    display: function()
    {
        if( this.collection.models.length > 0 )
        {
            this.clearList();
            this.viewpointer = [];
            this.tags        = [];

            this.$table.empty();

            _.each( this.collection.models, this.displayRow, this);

            var tags = this.tplTags({ tags: this.tags});

            this.$tagsList.html(tags);

            this.$tag      = this.$tagsList.find('li');
            this.$category = this.$categories.find('li');

            this.$tag.removeClass('active');
            this.$category.removeClass('active');
        }
    },

    displayRow: function( model )
    {
        var tags       = model.get('tags'),
            mergedTags = [];

        _.each(tags, function(tag)
        {
            this.tags[ tag.key ] = tag.value;
            mergedTags.push( tag.key );
        }, this);

        this.viewpointer[ model.cid ] = new $.Tapioca.Views.LibraryRow({
            model:     model,
            appslug:   this.appslug,
            tpl:       this.tplRow,
            parent:    this.$table,
            mergedTags: mergedTags
        });
    },

    upload: function()
    {
        $.Tapioca.Components.FileUpload.init({
            appslug: this.appslug
        });
    },

    clearList: function()
    {
        _.each( this.viewpointer, function( view )
        {
            view.close();
        }, this);
    },

    onClose: function()
    {
        this.collection.unbind('reset', this.display);
        this.clearList();
    }
});