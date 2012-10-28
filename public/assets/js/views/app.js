
$.Tapioca.Views.App = Backbone.View.extend({

    userShortcuts: false,
    appNav: false,
    id: 'tapioca-app',
    tagName: 'div',

    initialize: function()
    {
        this.render();

        var self = this;

        this.model.fetch({
            success: function()
            {
                self.loggedIn();
            },
            error: function()
            {
                self.notLoggedIn();
            }
        });

        $.Tapioca.Mediator.subscribe('user::loggedIn',    _.bind( this.loggedIn, this ) );
        $.Tapioca.Mediator.subscribe('user::notLoggedIn', _.bind( this.notLoggedIn, this ) );
        // $.Tapioca.Mediator.subscribe('data:loaded',       this.dataLoaded);
        // $.Tapioca.Mediator.subscribe('section:highlight', this.highlight);
    },

    render: function()
    {
        this.$el.appendTo('body');
    },

    dataLoaded: function()
    {
        this.appNav = new $.Tapioca.Views.AppNav();
        this.appNav.render();

        $.Tapioca.app.ready();

        this.highlight(document.location.href);

        $.Tapioca.Nanoscroller();
    },

    loggedIn: function()
    {
        if($.Tapioca.view)
            $.Tapioca.view.close();

        this.$el.html( $.Tapioca.Tpl.index );

        this.userShortcuts = new $.Tapioca.Views.UserShortcuts({
            model: $.Tapioca.Session
        });

        $('<div class="pane-content pane-content-one-app" />').appendTo('#apps-nav');

        if( $.Tapioca.Session.isAdmin() )
        {
            this.adminNav = new $.Tapioca.Views.AdminNav();
        }

        $.Tapioca.FetchModels();

        if(document.location.href == $.Tapioca.config.rootUrl)
            Backbone.history.navigate($.Tapioca.config.appUrl, true);
    },

    notLoggedIn: function()
    {
        if(this.userShortcuts)
            this.userShortcuts.close();

        if(this.appNav)
            this.appNav.close();

        if($.Tapioca.view)
            $.Tapioca.view.close();

        this.$el.html('')

        $.Tapioca.view = new $.Tapioca.Views.Login();
    },

    highlight: function(href)
    {
        // As main nav is not public
        // this function wrapp the true
        // function as a black box

        // TODO: don't depend on URL but a section

        // this.appNav.highlight(href);
    }
});
    