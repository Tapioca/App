
$.Tapioca.Views.Revisions = $.Tapioca.Views.Content.extend(
{
    tagName:      'ul',
    id:           'revisions',
    className:    '',
    viewPointers: [],

    initialize: function( options )
    {
        this.appslug  = options.appslug;
        this.baseUri  = options.baseUri;
        this.locale   = options.locale;
        this.users    = options.users;
        this.revision = options.revision;
        this.isNew    = options.isNew;
        this.cache    = [];

        this.$el.appendTo('#revisions-holder');

        this.tplRow   = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.revision );

        this.model.bind('change:revisions', this.update, this)

        return this;        
    },

    update: function()
    {
        // empty list
        this.$el.html('');

        this.isNew = false;

        this.render();
    },

    render: function()
    {
        if( !this.isNew )
        {
            var revisions = this.model.get('revisions'),
                active    = ( _.isUndefined( this.revision)) ? revisions.active[ this.locale.key ] : this.revision,
                list      = _.filter(revisions.list, function( rev )
                            {
                                rev.active = ( rev.revision == active );

                                return (rev.locale === this.locale.key);
                            }, this);
        }
        else
        {
            var list = [];
        }

        _.each( list, this.display, this);

        return this;
    },

    display: function( revision )
    {
        this.viewPointers[ revision.id ] = new $.Tapioca.Views.RevisionRow({
            revision: revision,
            tpl:      this.tplRow,
            $parent:  this.$el
        }).render();
    },

    onClose: function()
    {
        this.model.unbind('change:revisions');
    }
});