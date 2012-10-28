
$.Tapioca.Views.UserShortcuts = Backbone.View.extend({

    id: 'user-shortcuts',
    tagName: 'div',

    initialize: function()
    {
        this.$el.appendTo('#apps-nav');
        this.model.bind('change', this.render, this);

        this.render();
    },

    render: function()
    {
        var tpl = Handlebars.compile($.Tapioca.Tpl.app.nav['user-shortcuts']);
    
        this.$el.html( tpl( this.model.toJSON() ));

        return this;
    },

    onClose: function()
    {
        this.model.unbind('change', this.render);
    }

});