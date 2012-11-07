
$.Tapioca.Views.Library = $.Tapioca.Views.Content.extend(
{
    viewpointer: [],
    tags: [],
    $tags: null,

    initialize: function( options )
    {
        this.appslug   = $.Tapioca.appslug;
        this.tplRow    = Handlebars.compile( $.Tapioca.Tpl.app.container.library['index-row'] );
        this.tplTags   = Handlebars.compile( $.Tapioca.Tpl.components.tags );

        Handlebars.registerPartial('library-list',  $.Tapioca.Tpl.app.container.library['index-list'] );

        this.collection.bind('reset', this.display, this);

        this.$el.appendTo('#app-content');
    },

    render: function()
    {
        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.library.index ),
            html = tpl({
                appslug:    $.Tapioca.appslug,
                isAppAdmin: $.Tapioca.Session.isAdmin()
            });

        this.html( html );

        this.$table = this.$el.find('tbody');

        if( this.collection.isFetched() )
        {
            this.display();
        }


        return this;
    },

    display: function()
    {
        if( this.collection.models.length > 0 )
        {
            this.$table.empty();

            _.each( this.collection.models, this.displayRow, this);

            var tags      = this.tplTags({ tags: this.tags}),
                $tags     = $('#tags-list'),
                $category = $('#category-list'),
                self      = this;

            $tags.append(tags);

            this.$tags     = $tags.find('li');
            this.$category = $category.find('li');

            var filterTags = function(event)
            {
                self.$tags.removeClass('active');

                var key = $(event.target).addClass('active').attr('data-tag');

                for( var i in self.viewpointer )
                {
                    self.viewpointer[ i ].filterTags( key );
                }
            };

            var filterCategory = function(event)
            {
                self.$category.removeClass('active');

                var key = $(event.target).addClass('active').attr('data-category');

                for( var i in self.viewpointer )
                {
                    self.viewpointer[ i ].filterCategory( key );
                }
            };

            this.$tags.click(filterTags)
            this.$category.click(filterCategory)
        }
    },

    displayRow: function( model )
    {
        var tags       = model.get('tags'),
            mergedTags = [];

        _.each(tags, function(tag)
        {
            this.tags.push(tag);
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

    onClose: function()
    {
        this.collection.unbind('reset', this.display);

        _.each( this.viewpointer, function( view )
        {
            view.close();
        }, this);
    }
});