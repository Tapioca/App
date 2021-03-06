
$.Tapioca.Controllers.Collection = {

    Home: function( appslug, namespace ) 
    {
        $.Tapioca.appslug = appslug;

        var collection = $.Tapioca.UserApps[ appslug ].collections.get( namespace ),
            abstracts  = $.Tapioca.UserApps[ appslug ].data[ namespace ].abstracts,
            baseUri    = $.Tapioca.app.setRoute('appCollectionHome', [ appslug, namespace ] );

        if( !abstracts.isFetched() )
        {
            abstracts.fetch();
        }

        $.Tapioca.view = new $.Tapioca.Views.Collection({
            model:     collection,
            abstracts: abstracts,
            baseUri:   baseUri
        }).render();
    },

    Ref: function( appslug, namespace, ref)
    {
        $.Tapioca.appslug = appslug;

        var params = Backbone.history.getQueryParameters(),
            app    = $.Tapioca.UserApps[ appslug ];

        // get locale
        if(!_.isUndefined(params.l))
        {
            app.app.setWorkingLocale( params.l );
        }

        var collection   = app.collections.get( namespace ),
            abstracts    = app.data[ namespace ].abstracts,
            // users        = app.users,
            locale       = app.app.getWorkingLocale(),
            baseUri      = $.Tapioca.app.setRoute('appCollectionRef', [ appslug, namespace, ref ] );
            isNew        = ( ref === 'new' ),
            fetchOptions = $.param( $.extend({}, params) ),
            revision     = params.r,
            docOptions   = {
                appslug:   appslug,
                namespace: namespace,
                locale:    locale.key
            },
            docAttributes = {};


        if( !isNew )
            docAttributes._ref = ref;

        var doc =  new $.Tapioca.Models.Document( docAttributes, docOptions )

        $.Tapioca.view = new $.Tapioca.Views.Document({
            appslug:    appslug,
            namespace:  namespace,
            ref:        ref,
            revision:   revision,
            locale:     locale,

            isNew:      isNew,
            baseUri:    baseUri,

            schema:     collection,
            abstracts:  abstracts,
            doc:        doc,
            docOptions: fetchOptions

        });
    },

    New: function( appslug )
    {
        $.Tapioca.appslug = appslug;

        var collection = new $.Tapioca.Models.Collection({
            appslug: appslug
        });

        $.Tapioca.view = new $.Tapioca.Views.CollectionEdit({
            isNew: true,
            model: collection
        }).render();

    },

    Edit: function( appslug, namespace)
    {
        $.Tapioca.appslug = appslug;

        var collection = $.Tapioca.UserApps[ appslug ].collections.get( namespace );

        $.Tapioca.view = new $.Tapioca.Views.CollectionEdit({
            isNew: false,
            model: collection
        });

        if( !collection.hasSchema() )
        {
            collection.fetch({
                success: function()
                {
                    $.Tapioca.view.render();
                }
            })
        }
        else
        {
            $.Tapioca.view.render();
        }

    }
};