
$.Tapioca.Views.AdminAppListRow = Backbone.View.extend(
{
    tagName: 'tr',

    initialize: function( options )
    {
        this.tpl = options.tpl;

        this.$el.appendTo( options.parent );

        this.model.bind('destroy', this.close, this);

        return this;
    },

    events: {
        'click .btn-delete-trigger': 'delete'
    },

    delete: function(event)
    {
        this.model.delete();
    },

    render: function()
    {
        this.$el.html( this.tpl( this.model.toJSON() ) );

        return this;
    },

    onClose: function()
    {
        this.model.unbind('destroy', this.close);
    }
});