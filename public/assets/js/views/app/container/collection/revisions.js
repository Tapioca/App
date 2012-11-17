
$.Tapioca.Views.Revisions = $.Tapioca.Views.Content.extend(
{
    initialize: function( options )
    {
        this.appslug  = options.appslug;
        this.baseUri  = options.baseUri;
        this.locale   = options.locale;
        this.users    = options.users;
        this.revision = options.revision;
        this.isNew    = options.isNew;
        this.cache    = [];

        return this;        
    },

    getUser: function( id )
    {
        if ( !this.cache[ id ] )
        {
            var user = this.users.get( id );

            this.cache[ id ] = {
                id:   id,
                name: user.get('name')
            };
        }

        return this.cache[ id ];
    },

    render: function()
    {
        if( !this.isNew )
        {
            var revisions = this.model.get('revisions'),
                active    = ( _.isUndefined( this.revision)) ? revisions.active[ this.locale.key ] : this.revision,
                list      = _.filter(revisions.list, function( rev )
                            {
                                return (rev.locale === this.locale.key);
                            }, this);

            _.each(list, function( rev )
            {
                if( rev.revision == active )
                    rev.active = true;

                rev.user = this.getUser( rev.user );

            }, this);
        }
        else
        {
            var list = [];
        }


        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.revision ),
            html = tpl({
                appslug:    this.appslug,
                baseUri:    this.baseUri,
                revisions:  list
            });

        this.$revisions = $('#revisions');

        this.$revisions.append( html );

        return this;
    }
});