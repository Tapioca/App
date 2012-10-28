
$.Tapioca.Views.AdminAppEdit = $.Tapioca.Views.FormView.extend(
{
    initialize: function( options )
    {
        this.isNew = options.isNew;
        
        return this;
    },

    events: _.extend({
        'keyup #slug': 'slugify',
        'keyup #name': 'slugify'
    }, $.Tapioca.Views.FormView.prototype.events),

    render: function()
    {
        this.$el.appendTo('#app-content');

        var model = this.model.toJSON();

        model.isNew     = this.isNew;
        model.pageTitle = ( this.isNew ) ?
                            __('label.add_app') :
                            $.Tapioca.I18n.get('title.edit_app', this.model.get('name'));

        // Team users's name
        _.each(model.team, function(user)
        {
            var userModel = $.Tapioca.Users.get( user.id );

            user.name = userModel.get('name');

        }, this);

        // Admin users's name
        var admins = model.admins;

        model.admins = [];

        _.each( admins, function( user )
        {
            var userModel = $.Tapioca.Users.get( user );

            user = {
                id: user,
                name: userModel.get('name')
            };

            model.admins.push( user );

        }, this);

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.admin.app.edit ),
            self = this,
            html = tpl( model );

        this.html( html, 'app-form');

        this.$slug = $('#slug');

        var userEmails = $.Tapioca.Users.pluck( 'email' );

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
                source : userEmails,
                minLength : 2,
                select: function(event, ui)
                {
                    var selectedModel = $.Tapioca.Users.where( { email: ui.item.value } )[0];
                    console.log( selectedModel );
                    // var view = new SelectionView({model: selectedModel});
                    // view.render();
                }
            });

        return this;
    },

    slugify: function( event )
    {
        if( this.isNew && event.target.value != '' )
        {
            this.$slug.val( $.Tapioca.Components.Form.slugify( event.target.value ) );
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
            _data['slug'] = $('#slug').val();
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
    }
});