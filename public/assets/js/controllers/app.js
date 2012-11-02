
$.Tapioca.Controllers.App = {

    Home: function( appslug ) 
    {
        $.Tapioca.appslug = appslug;

        $.Tapioca.view = new $.Tapioca.Views.AppIndex().render();
    },
};