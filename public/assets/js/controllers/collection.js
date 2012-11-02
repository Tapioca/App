
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

console.log('Collection Home: ' +namespace+', ref: '+ref);
    },

    Edit: function( appslug, namespace)
    {

    }
};