
$.Tapioca.Views.SearchResult = $.Tapioca.Views.Content.extend(
{
    initialize: function()
    {
        $.Tapioca.Mediator.subscribe('search::query',   _.bind( this.display,   this ) );

        this.app    = $.Tapioca.UserApps[ $.Tapioca.appslug ];
        this.tplRow = Handlebars.compile( $.Tapioca.Tpl.app.container.search.row );
        this.cached = [];

        return this;
    },
    
    render: function()
    {
        this.$el.appendTo('#app-content');

        this.html( $.Tapioca.Tpl.app.container.search.index );

        this.$queryTerm = $('#search-results-term').find('span');
        this.$table     = this.$el.find('tbody');

        return this;
    },

    getCache: function( str )
    {
        if( _.isUndefined( this.cached[ str ] ) )
        {
            this.cached[ str ] = this.app.collections.get( str ).get('name');
        }

        return this.cached[ str ];
    },

    display: function( query )
    {
        this.$queryTerm.text( query );

        var self    = this,
            results = this.app.searchIndex.search( query ).map(function ( result )
            {
                return self.app.searchValues.filter(function (d){ return d._ref === result.ref })[0];
            });

        _.each( results, function( row )
        {
            row.name = this.getCache( row.namespace );
            row.url  = $.Tapioca.app.setRoute('appCollectionRef', [ $.Tapioca.appslug, row.namespace, row._ref ])

            for (var prop in row.digest)
            {
                row.title = row.digest[ prop ];
                break;
            }
            
        }, this);

        this.$table.html( this.tplRow({ row: results }));
    }
});