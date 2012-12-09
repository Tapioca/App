
$.Tapioca.Views.AdminUserEdit = $.Tapioca.Views.FormView.extend(
{
    initialize: function( options )
    {
        this.isNew = options.isNew;
        
        return this;
    },

    events: _.extend({
        'click #password-generator': 'generatePassword'
    }, $.Tapioca.Views.FormView.prototype.events),

    render: function()
    {
        this.$el.appendTo('#app-content');

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.admin.user.edit ),
            html = tpl( this.model.toJSON() );

        this.html( html, 'app-form');

        // validation rules
        
        this.form = document.getElementById('tapioca-user-form');

        var _rules = [
            {
                display: __('label.user_name'),
                name:    'name',
                rules:   'required'
            },
            {
                display: __('label.user_email'),
                name:    'email',
                rules:   'required|valid_email'
            },
            {
                display: __('label.user_password'),
                name:    'password',
                rules:   'min_length[6]'
            }
        ];

        if( this.isNew )
        {
            _rules[2].rules += '|required';
        }
        
        this.addRules( _rules );   

        return this;
    },

    generatePassword: function() 
    {
        var length = 8,
            charset = 'abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            retVal = '';

        for (var i = 0, n = charset.length; i < length; ++i)
        {
            retVal += charset.charAt(Math.floor(Math.random() * n));
        }
        
        $('#password').val( retVal );
    },

    submit: function()
    {
        // reset warning and feedback
        // this.$el.find('input').removeClass('warning');
        
        if( this.validateForm() )
        {
            var _data = {
                    email: $('#email').val(), 
                    name:  $('#name').val(),
                    admin: $('#admin').is(':checked')
                },
                password = $('#password').val(),
                $btn     = $('#profile-form-save'),
                valid    = true, // flag for pasword
                self     = this;

            if( !_.isBlank( password ) )
            {
                _data['password'] = password;
            }

            this.model.set(_data);
            
            if(valid)
            {
                // Sets button state to loading - disables button and swaps text to loading text
                this.$btnSubmit.button('loading');

                this.model.save({}, {
                    success: function(model, response)
                    {
                        $('#password').val('');

                        self.model.unset('password', {silent: true});

                        self.resetForm();

                        if(self.isNew)
                        {
                            $.Tapioca.Users.add( model )

                            var href = $.Tapioca.app.setRoute('adminUserEdit', [ model.get('id') ] )

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
        } // validation
    }
});