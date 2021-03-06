
$.Tapioca.Views.AppAdminUserRow = Backbone.View.extend(
{
    tagName: 'tr',

    initialize: function( options )
    {
        this.tpl      = options.tpl;
        this.operator = options.operator;
        this.user     = options.user;
        this.apiUrl   = $.Tapioca.config.apiUrl + $.Tapioca.appslug +'/user/' + this.user.id;

        this.$el.appendTo( options.parent );

        return this;
    },

    events: {
        'click .btn-delete-trigger': 'revoke',
        'click .dropdown-menu a':    'role'
    },

    revoke: function(event)
    {
        var self  = this;

        var post = $.ajax({
            url:      this.apiUrl,
            dataType: 'json',
            type:     'DELETE'
        });

        post.done( _.bind( this.confirmDelete, this) );

        post.fail(function( p )
        {
            console.log( p )
        });
    },

    confirmDelete: function( tokenObj )
    {
        this.tokenObj = tokenObj;

        var user = $.Tapioca.Users.get( this.user.id ),
            text = $.Tapioca.I18n.get('delete.question_remove', user.get('name')),
            self = this;

        $.Tapioca.Dialog.confirm( _.bind( this.delete, this ), { text: text });
    },

    delete: function()
    {
        var self = this,
            post = $.ajax({
                url:      this.apiUrl + '?token=' + this.tokenObj.token,
                dataType: 'json',
                type:     'DELETE'
            });

        post.done(function( p )
        {
            $.Tapioca.UserApps[ $.Tapioca.appslug ].app.set( 'team', p );

            // TODO: to clean, as users, one collection
            if( $.Tapioca.Session.isAdmin() )
            {
                var app = $.Tapioca.Apps.get( $.Tapioca.appslug );
                app.set( 'team', p );
            }

            self.render()
        });

        post.fail(function( p )
        {
            console.log( p )
        });
    },

    role: function( event )
    {
        var _role = $( event.target ).attr('data-role'),
            self  = this;

        var post = $.ajax({
            url:      this.apiUrl,
            data:     JSON.stringify({role: _role}),
            dataType: 'json',
            type:     'PUT'
        });

        post.done(function( p )
        {
            // TODO: to clean, as users, one collection
            $.Tapioca.UserApps[ $.Tapioca.appslug ].app.set( 'team', p );
            if( $.Tapioca.Session.isAdmin() )
            {
                var app = $.Tapioca.Apps.get( $.Tapioca.appslug );
                app.set( 'team', p );
            }
            self.render()
        });

        post.fail(function( p )
        {
            console.log( p )
        });
    },

    render: function()
    {
        var user = $.Tapioca.Users.get( this.user.id );
            data = {
                name:     user.get('name'),
                avatar:   user.get('avatar'),
                id:       this.user.id,
                appslug:  $.Tapioca.appslug,
                operator: this.operator
            };

        this.$el.html( this.tpl( data ) );

        return this;
    }
});