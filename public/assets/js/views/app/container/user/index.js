
$.Tapioca.Views.AppAdminUsers = $.Tapioca.Views.Content.extend(
{
    viewspointer: [],

    initialize: function()
    {
        this.$el.appendTo('#app-content');

        this.appslug  = $.Tapioca.appslug;
        this.operator = $.Tapioca.Session.get('id');
        this.tplRow   = Handlebars.compile( $.Tapioca.Tpl.app.container.user.row );

        this.collection.bind('reset', this.displayUsers, this);
        
        // $.Tapioca.UserApps[ $.Tapioca.appslug ].app.bind( 'change:team', this.displayUsers, this);
    },

    render: function()
    {
        var appName = $.Tapioca.UserApps[ this.appslug ].app.get('name'),
            data = {
                pageTitle: $.Tapioca.I18n.get('title.app_users', appName),
                appslug:   this.appslug
            };

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.user.index ),
            html = tpl( data );

        this.html( html );

        this.$table = this.$el.find('tbody');

        if( this.collection.isFetched() )
        {
            this.displayUsers();
        }

        return this;
    },

    displayUsers: function()
    {
        this.$table.empty();

        var team  = $.Tapioca.UserApps[ this.appslug ].app.get('team');
        
        _.each( team, this.displayUserRow, this);
    },

    displayUserRow: function( user )
    {
        this.viewspointer[ user.id ] = new $.Tapioca.Views.AppAdminUserRow({
            user:     user,
            parent:   this.$table,
            tpl:      this.tplRow,
            operator: this.operator
        });

        this.viewspointer[ user.id ].render();

        ++this.index;
    },

    onClose: function()
    {
        this.collection.unbind('reset', this.displayUsers);

        _.each( this.viewspointer, function( view )
        {
            view.close();

        }, this);
    }
});