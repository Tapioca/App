
$.Tapioca.Views.Nav = Backbone.View.extend(
{
    id:          'apps-nav',
    className:   'pane nano',
    tagName:     'div',
    viewpointer: [],

    initialize: function()
    {
        this.$el.prependTo('#main');

        this.render();

        return this;
    },

    render: function()
    {
        this.$el.html( $.Tapioca.Tpl.app.nav.index );

        this.renderUser();

        $('<div class="pane-content pane-content-one-app" />').appendTo('#apps-nav');

        this.renderAdmin();

        this.$paneContent = this.$el.find('div.pane-content');

        return this;
    },

    renderUser: function()
    {
        this.userShortcuts = new $.Tapioca.Views.NavUser({
            model: $.Tapioca.Session
        });

        return this;
    },

    renderAdmin: function()
    {
        if( $.Tapioca.Session.isAdmin() )
        {
            this.adminNav = new $.Tapioca.Views.NavAdmin();
        }
    },

    renderApps: function()
    {
        for( var appslug in $.Tapioca.UserApps )
        {
            var app      = $.Tapioca.UserApps[ appslug ],
                _options = {
                    appslug:     appslug,
                    parent:      this.$paneContent,
                    model:       app.app,
                    collections: app.collections
                };

            this.viewpointer[ appslug ] = new $.Tapioca.Views.NavApp( _options ).render();
        }

        var $appNav = this.$el.find('div.app-nav');
        
        if( $appNav.length > 1 )
        {
            this.$paneContent.removeClass('pane-content-one-app');
        }

        $appNav.eq(0).addClass('app-nav-active');
    },

    highlight: function(href)
    {
        // As main nav is not public
        // this function wrapp the true
        // function as a black box

        // TODO: don't depend on URL but a section

        // this.appNav.highlight(href);
    },

    onClose: function()
    {
        if(this.userShortcuts)
            this.userShortcuts.close();

        if(this.adminNav)
            this.adminNav.close();
    }
})