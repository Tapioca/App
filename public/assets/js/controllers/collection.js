
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

console.log('Collection Ref: ' +namespace+', ref: '+ref);
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

        if( collection.hasSchema() )
        {
            collection.fetch({
                success: function()
                {
                    $.Tapioca.view.render();
                }
            })
        }

        $.Tapioca.view = new $.Tapioca.Views.CollectionEdit({
            isNew: false,
            model: collection
        });

    }
};