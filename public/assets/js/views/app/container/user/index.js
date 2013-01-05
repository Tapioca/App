
$.Tapioca.Views.AppAdminUsers = $.Tapioca.Views.Content.extend(
{
    viewpointer: [],

    initialize: function()
    {
        this.$el.appendTo('#app-content');

        this.appslug  = $.Tapioca.appslug;
        this.operator = $.Tapioca.Session.get('id');
        this.tplRow   = Handlebars.compile( $.Tapioca.Tpl.app.container.user.row );
    },

    render: function()
    {
        var appName = this.model.get('name'),
            data = {
                pageTitle: $.Tapioca.I18n.get('title.app_users', appName),
                appslug:   this.appslug
            };

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.user.index ),
            html = tpl( data );

        this.html( html );

        this.$table = this.$el.find('tbody');

        this.displayUsers();

        return this;
    },

    displayUsers: function()
    {
        this.clearUsers();

        _.each( this.model.get('team'), function( user )
        {
            this.viewpointer[ user.id ] = new $.Tapioca.Views.AppAdminUserRow({
                                                user:     user,
                                                parent:   this.$table,
                                                tpl:      this.tplRow,
                                                operator: this.operator
                                            }).render();
        }, this);
    },

    clearUsers: function()
    {
        for( var i in this.viewpointer)
        {
            this.viewpointer[ i ].close();  
        }
    },

    onClose: function()
    {
        this.clearUsers();
    }
});