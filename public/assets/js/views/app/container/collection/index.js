
$.Tapioca.Views.Collection = $.Tapioca.Views.Content.extend(
{
    viewpointer: [],

    initialize: function( options )
    {
        this.appslug   = $.Tapioca.appslug;
        this.abstracts = options.abstracts;
        this.baseUri   = options.baseUri;
        this.namespace = this.model.get('namespace');
        this.tplRow    = Handlebars.compile( $.Tapioca.Tpl.app.container.collection['index-row'] );

        var params = Backbone.history.getQueryParameters();

        this.abstracts.bind('reset', this.display, this);

        // get locale
        if(!_.isUndefined(params.l))
        {
            $.Tapioca.UserApps[ this.appslug ].app.setWorkingLocale( params.l );
        }

        this.locale = $.Tapioca.UserApps[ this.appslug ].app.getWorkingLocale();

        this.$el.appendTo('#app-content');
    },

    render: function()
    {
        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.index ),
            html = tpl({
                appslug:    $.Tapioca.appslug,
                isAppAdmin: $.Tapioca.Session.isAdmin(),
                locale:     this.locale,
                baseUri:    this.baseUri,
                name:       this.model.get('name'),
                namespace:  this.model.get('namespace'),
                digest:     this.model.get('digest')
            });

        this.html( html );

        this.$table = this.$el.find('tbody');

        if( this.abstracts.isFetched() )
        {
            this.display();
        }

        return this;
    },

    display: function()
    {
        _.each( this.abstracts.models, this.displayRow, this)
    },

    displayRow: function( model )
    {
        this.viewpointer[ model.cid ] = new $.Tapioca.Views.CollectionRow({
            model:     model,
            appslug:   this.appslug,
            namespace: this.namespace,
            tpl:       this.tplRow,
            locale:    this.locale,
            parent:    this.$table
        });
    },

    onClose: function()
    {
        this.abstracts.unbind('reset', this.display);

        // _.each( this.viewpointer, function( view )
        // {
        //     view.close();
        // }, this);
        for( var i in this.viewpointer)
        {
            this.viewpointer[ i ].close();  
        }
    }
});