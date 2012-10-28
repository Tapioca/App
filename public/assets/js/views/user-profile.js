
$.Tapioca.Views.UserProfile = $.Tapioca.Views.FormView.extend(
{
    render: function()
    {
        this.$el.appendTo('#app-content');

        var tpl  = Handlebars.compile( $.Tapioca.Tpl['user-profile'] ),
            self = this,
            html = tpl( this.model.toJSON() );

        this.html( html, 'app-form');

    },

    submit: function()
    {
        // reset alert and feedback
        this.$el.find('input').removeClass('alert');
        // $('#unik-login-feedback').html('');

        var _data = {
                email: $('#email').val(), 
                name:  $('#name').val()
            },
            password = $('#new-pass').val(),
            conf     = $('#conf-pass').val(),
            old      = $('#old-pass').val(),
            $btn     = $('#profile-form-save'),
            valid    = true, // flag for pasword
            self     = this;

        if(!_.isBlank(password))
        {
            if((password == conf) && !_.isBlank(old))
            {
                _data['newpass'] = password;
                _data['oldpass'] = old;
            }
            else
            {
                valid = false;

                $('#new-pass').add('#conf-pass').addClass('alert');
                
                // $.Unik.Notification.feedback({
                //     title: 'Problem',
                //     text: 'new password and confirm not matching'
                // });
            }
        }

        this.model.set(_data);
        
        if(valid)
        {
            // Sets button state to loading - disables button and swaps text to loading text
            this.$btnSubmit.button('loading');

            this.model.save({}, {
                success: function(model, response)
                {
                    $('#new-pass').val('');
                    $('#conf-pass').val('');
                    $('#old-pass').val('');

                    // self.model.unset('oldpass', {silent: true});
                    // self.model.unset('newpass', {silent: true});

                    self.resetForm();

                    // $.Unik.Notification.feedback({
                    //     text: 'Your profile have been update!'
                    // });

                    // $.Unik.Notification.spread( 'update', 'User', self.model.get('id'));
                },
                error: function(model, response)
                {
                    self.model.set( self.model.previousAttributes() );

                    $btn.button('reset');

                    // var json = $.parseJSON(response.responseText);

                    // $.Unik.Notification.feedback({
                    //     title: "Problem",
                    //     text: json.message
                    // });
                }
            });


        }
        else
        {
            this.model.set( this.model.previousAttributes() );
        }
    }
});