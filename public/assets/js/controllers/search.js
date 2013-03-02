
$.Tapioca.Controllers.Search = {

    Home: function( appslug, category ) 
    {
        $.Tapioca.appslug = appslug;

        $.Tapioca.view = new $.Tapioca.Views.SearchResult().render();
    }
};