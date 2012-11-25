
$.Tapioca.Views.EmbedFile = Backbone.View.extend(
{
    initialize: function( options )
    {
        this.appslug    = $.Tapioca.appslug;
        this.tplRow     = Handlebars.compile( $.Tapioca.Tpl.app.container.collection['embed-file-row'] );
        this.tplTags    = Handlebars.compile( $.Tapioca.Tpl.components.tags );
        this.catTrigger = options.category; 
        this.$form      = options.form;

        var wHeight = $(window).height();
        var dHeight = wHeight * 0.8;

        $.Tapioca.Dialog.open({
            height: dHeight,
            width:  '80%'
        });

        this.$el.appendTo('#dialog-modal');

        this.$el.html( $.Tapioca.Tpl.app.container.collection['embed-file-list'] );

        this.$table      = this.$el.find('tbody');
        this.$tagsList   = $('#tags-list');
        this.$categories = $('#category-list');

        this.render();

        return this;
    },

    events: {
        'click #tags-list li':     'filterTags',
        'click #category-list li': 'filterCategory'
    },

    render: function()
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

            if( !_.isUndefined( this.catTrigger ) )
            {
                this.filterCategory( this.catTrigger );
            }
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

        if( !_.isUndefined( event.target) )
        {
            var key = $(event.target).addClass('active').attr('data-category');
        }
        else
        {
            var key = event;

            this.$category.filter('[data-category="'+ event +'"]').addClass('active');
        }

        for( var i in this.viewpointer )
        {
            this.viewpointer[ i ].filterCategory( key );
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
            model:      model,
            appslug:    this.appslug,
            tpl:        this.tplRow,
            parent:     this.$table,
            mergedTags: mergedTags,
            form:       this.$form
        });
    },

    clearList: function()
    {
        _.each( this.viewpointer, function( view )
        {
            view.close();
        }, this);
    }

});