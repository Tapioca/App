
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

        $.Tapioca.Mediator.subscribe('user::loggedIn',    _.bind( this.loggedIn,    this ) );
        $.Tapioca.Mediator.subscribe('user::notLoggedIn', _.bind( this.notLoggedIn, this ) );
        $.Tapioca.Mediator.subscribe('data::loaded',      _.bind( this.dataLoaded,  this ) );
        // $.Tapioca.Mediator.subscribe('section:highlight', this.highlight);
    },

    render: function()
    {
        this.$el.appendTo('body');
    },

    dataLoaded: function()
    {
        $.Tapioca.app.ready();

        this.appNav.renderApps();

        // this.highlight(document.location.href);

        $.Tapioca.Nanoscroller();
    },

    loggedIn: function()
    {
        if($.Tapioca.view)
            $.Tapioca.view.close();

        this.$el.html( $.Tapioca.Tpl.index );

        this.appNav    = new $.Tapioca.Views.Nav();
        this.appSearch = new $.Tapioca.Views.SearchForm();

        $.Tapioca.Bootstrap();

        if(document.location.href == $.Tapioca.config.rootUrl)
            Backbone.history.navigate($.Tapioca.config.appUrl, true);
    },

    notLoggedIn: function()
    {
        if(this.appNav)
            this.appNav.close();

        if($.Tapioca.view)
            $.Tapioca.view.close();

        this.$el.html('')

        $.Tapioca.view = new $.Tapioca.Views.Login();
    }
});
    