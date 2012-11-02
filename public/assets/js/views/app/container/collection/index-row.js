
$.Tapioca.Views.CollectionRow = Backbone.View.extend(
{
    tagName: 'tr',

    initialize: function( options )
    {
        this.appslug   = options.appslug;
        this.namespace = options.namespace;
        this.tpl       = options.tpl;
        this.locale    = options.locale;

        this.$el.appendTo( options.parent );

        this.render();

        return this;
    },


    render: function()
    {
        var model = this.model.toJSON(),
            args  = [ this.appslug, this.namespace, model._ref ];

        model.appslug    = this.appslug;
        model.namespace  = this.namespace;
        model.locale     = this.locale;
        model.isAppAdmin = $.Tapioca.Session.isAdmin();
        model.uri        = $.Tapioca.app.setRoute('appCollectionRef', args);

        this.$el.html( this.tpl( model ) );

        return this;
    }
});