
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
        'click .btn-delete-trigger': 'remove',
        'click .dropdown-menu a':    'role'
    },

    remove: function(event)
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

        var user = $.Tapioca.UserApps[ $.Tapioca.appslug ].users.get( this.user.id ),
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
            $.Tapioca.UserApps[ $.Tapioca.appslug ].app.set( 'team', p );
            self.render()
        });

        post.fail(function( p )
        {
            console.log( p )
        });
    },

    render: function()
    {
        var user = $.Tapioca.UserApps[ $.Tapioca.appslug ].users.get( this.user.id );
            data = {
                name:     user.get('name'),
                avatar:   user.get('avatar'),
                id:       this.user.id,
                appslug:  $.Tapioca.appslug,
                operator: this.operator
            };

        this.$el.html( this.tpl( data ) );

        return this;
    },

    onClose: function()
    {
    }
});