
$.Tapioca.Controllers.Library = {

    Home: function( appslug ) 
    {
        $.Tapioca.appslug = appslug;

        var library = $.Tapioca.UserApps[ appslug ].library;

        if( !library.isFetched() )
        {
            library.fetch();
        }

        $.Tapioca.view = new $.Tapioca.Views.Library({
            collection: library
        }).render();
    },

    Ref: function( appslug, basename, ext)
    {
        $.Tapioca.appslug = appslug;

console.log('Library Ref: ' +basename+'.'+ext);
    }
};