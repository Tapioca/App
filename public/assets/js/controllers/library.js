
$.Tapioca.Controllers.Library = {

    Home: function( appslug, category ) 
    {
        $.Tapioca.appslug = appslug;

        var library = $.Tapioca.UserApps[ appslug ].library;

        if( !library.isFetched() )
        {
            library.fetch();
        }

        $.Tapioca.view = new $.Tapioca.Views.Library({
            collection: library,
            category:   category
        }).render();
    },

    Category: function( appslug, category ) 
    {
        $.Tapioca.Controllers.Library.Home( appslug, category);

    },

    Ref: function( appslug, basename, ext)
    {
        $.Tapioca.appslug = appslug;

console.log('Library Ref: ' +basename+'.'+ext);
    }
};