
$.Tapioca.Views.AdminUserListRow = Backbone.View.extend(
{
    tagName: 'tr',

    initialize: function( options )
    {
        this.tpl      = options.tpl;
        this.isMaster = options.isMaster;

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

    // admin can not edit admin profile
    // except Master admin
    isRestricted: function()
    {
        if( this.isMaster )
            return false;

        if( this.model.get('admin') )
            return true;
    },

    render: function()
    {
        var model = this.model.toJSON();

        model.restricted = this.isRestricted();

        this.$el.html( this.tpl( model ) );

        return this;
    },

    onClose: function()
    {
        this.model.unbind('destroy', this.close);
    }
});