
$.Tapioca.Views.AdminAppTeamRow = Backbone.View.extend(
{
    tagName: 'tr',

    initialize: function( options )
    {
        this.parent  = options.parent;
        this.tpl     = options.tpl;
        this.user    = options.user;
        this.appslug = options.appslug;
        this.apiUrl  = $.Tapioca.config.apiUrl + this.appslug +'/user/' + this.user.id;

        this.$el.appendTo( options.$parent );

        return this;
    },

    events: {
        'click .btn-delete-trigger': 'revoke'
        // 'click .dropdown-menu a':    'role'
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

            var app = $.Tapioca.Apps.get( this.appslug );
            app.set( 'team', p );

            self.render()
        });

        post.fail(function( p )
        {
            console.log( p )
        });
    },

    render: function()
    {
        this.$el.html( this.tpl( this.user ) );

        return this;
    }
});