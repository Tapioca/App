
$.Tapioca.Views.AdminAppEdit = $.Tapioca.Views.FormView.extend(
{
    viewpointer: [],

    initialize: function( options )
    {
        this.isNew     = options.isNew;
        this.tplLocale = Handlebars.compile( $.Tapioca.Tpl.components.locales );
        this.tplRow    = Handlebars.compile( $.Tapioca.Tpl.admin.app['team-row'] );
        this.appslug   = this.model.get('slug');

        this.model.bind('change:team', this.team, this);

        return this;
    },

    events: _.extend({
        'keyup #slug': 'slugify',
        'keyup #name': 'slugify'
    }, $.Tapioca.Views.FormView.prototype.events),

    addRepeatNode: function()
    {
        this.$el.find('ul.input-repeat-list').append( this.tplLocale({}) );
    },

    render: function()
    {
        this.$el.appendTo('#app-content');

        var model = this.model.toJSON();

        model.operator  = $.Tapioca.Session.get('id');
        model.isNew     = this.isNew;
        model.pageTitle = ( this.isNew ) ?
                            __('label.add_app') :
                            $.Tapioca.I18n.get('title.edit_app', this.model.get('name'));

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.admin.app.edit ),
            self = this,
            html = tpl( model );

        this.html( html, 'app-form');

        this.$slug = $('#slug');
        this.$team = $('#app-team').find('tbody');

        var userEmails = $.Tapioca.Users.pluck( 'email' );
        // var userNames  = $.Tapioca.Users.pluck( 'name' );

        $('#new-user')
            .keypress(function(event)
            {
                // prevent form submit
                if (event.keyCode == 13)
                {
                    event.preventDefault();
                    event.stopPropagation();    
                }
            })
            .autocomplete(
            {
                source : userEmails, //.concat(userNames),
                minLength : 2,
                select: function(event, ui)
                {
                    // TODO: prevent add user already in app;
                    var selectedModel = $.Tapioca.Users.where( { email: ui.item.value } )[0];

                    self.user('POST', selectedModel.get('id'));
                }
            });

        this.team();
        this.$el.find('.dropdown-toggle').dropdown();

        return this;
    },

    slugify: function( event )
    {
        if( this.isNew && event.target.value != '' )
        {
            this.$slug.val( $.Tapioca.Components.Form.slugify( event.target.value ) );
        }
    },

    user: function( method, userId )
    {
        var url  = this.model.url() + '/user/' + userId,
            self = this,
            hxr  = $.ajax({
                url:      url,
                dataType: 'json',
                type:     method,
                success: function(data)
                {
                    self.model.fetch()
                }
            });
    },

    team: function()
    {
        this.closeTeam();

        // Team users's name
        _.each(this.model.get('team'), function(user)
        {
            var userModel    = $.Tapioca.Users.get( user.id );

            user.disabled    = ( user.role == '_REVOKED_ACCESS_' );
            user.avatar      = userModel.get('avatar');
            user.name        = userModel.get('name');
            user.roleDisplay = __('roles.'+ user.role);

            this.viewpointer[ user.id ] = new $.Tapioca.Views.AdminAppTeamRow({
                user:        user,
                parent:      this,
                $parent:     this.$team,
                appslug:     this.appslug,
                tpl:         this.tplRow
            }).render();

        }, this);

    },

    closeTeam: function()
    {
        for( var i in this.viewpointer)
        {
            this.viewpointer[ i ].close();  
        }
    },

    submit: function()
    {
        // reset warning and feedback
        this.$el.find('input').removeClass('warning');

        var _data = {
                name:  $('#name').val()
            },
            $btn     = $('#app-form-save'),
            valid    = true, // flag for first admin
            self     = this;

        if( this.isNew )
        {
            _data['slug-suggest'] = $('#slug').val();
        }

        this.model.set(_data);
        
        if(valid)
        {
            // Sets button state to loading - disables button and swaps text to loading text
            this.$btnSubmit.button('loading');

            this.model.save({}, {
                success: function(model, response)
                {

                    self.resetForm();

                    if(self.isNew)
                    {
                        $.Tapioca.Apps.add( model )

                        var href = $.Tapioca.app.setRoute('adminAppEdit', [ model.get('slug') ] )

                        Backbone.history.navigate( href );
                    }

                },
                error: function(model, response)
                {
                    self.model.set( self.model.previousAttributes() );

                    self.resetForm();
                }
            });
        }
        else
        {
            this.model.set( this.model.previousAttributes() );
            self.resetForm();
        }
    },

    onClose: function()
    {
        this.closeTeam();
    }
});