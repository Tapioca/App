
$.Tapioca.Views.NavApp = Backbone.View.extend(
{
    className:   'app-nav',
    tagName:     'div',

    initialize: function( options )
    {
        this.collections = options.collections;

        this.$el.appendTo( options.parent );
    },

    render: function()
    {
        var model = this.model.toJSON(),
            tpl   = Handlebars.compile( $.Tapioca.Tpl.app.nav.app );

        model.isAppAdmin = this.model.isAppAdmin();
    
        this.$el.html( tpl( model ));

        return this;
    }
});