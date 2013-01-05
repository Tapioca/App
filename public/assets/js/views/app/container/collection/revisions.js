
$.Tapioca.Views.Revisions = $.Tapioca.Views.Content.extend(
{
    tagName:      'ul',
    id:           'revisions',
    className:    '',
    viewpointer: [],

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

    events: {
        'click a.revision-btn':     'setRevision',
        'click ul.dropdown-menu a': 'setStatus'
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

        var _html = this.tplRow({
            revisions: list
        });

        this.$el.html( _html );

        this.$listItem = this.$el.find(' > li');

        return this;
    },

    setStatus: function( event )
    {
        var $target   = $( event.target ),
            _revision = $target.parents('li[data-revision]').attr('data-revision'),
            _status   = parseInt( $target.attr('data-status') ),
            _url      = this.model.url() + '?l=' + this.locale.key + '&r=' + _revision, 
            _self     = this;

        var put = $.ajax({
            url:      _url,
            data:     JSON.stringify({status: _status}),
            dataType: 'json',
            type:     'PUT'
        });

        put.done( function( p )
        {
            // console.log( p )
            _self.model.set( p );
        });

        put.fail( this.error );
    },

    setRevision: function( event )
    {
        this.$listItem.removeClass('well').addClass('revision');

        $( event.target ).parents('li').removeClass('revision').addClass('well');
    },

    onClose: function()
    {
        this.model.unbind('change:revisions');
    }
});