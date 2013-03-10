
$.Tapioca.Views.AppAdminSettings = $.Tapioca.Views.FormView.extend(
{
    viewpointer: [],

    initialize: function()
    {
        this.$el.appendTo('#app-content');

        this.tplLocale = Handlebars.compile( $.Tapioca.Tpl.components.locales );

        this.appslug  = $.Tapioca.appslug;
    },


    events: _.extend({
        'change #storage':              'displayStorageOptions',
        'keyup :input[id^="storage."]': 'displayStorageTest',
        'click #storage-test':          'testStorageOptions'
    }, $.Tapioca.Views.FormView.prototype.events),

    render: function()
    {
        var storage = this.model.getStorage()
          , model   = this.model.toJSON()
          , ext   = this.model.get('library.extwhitelist');

        model.pageTitle    = $.Tapioca.I18n.get('title.edit_app', this.model.get('name'));
        model.extWhitelist = ext.join(', ');

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.settings.index ),
            html = tpl( model );

        this.html( html );

        this.$storageSelector = $('#storage');
        this.$storageOptions  = $('#storage-data').find('div.control-group');
        this.$storageTestBtn = $('#storage-test-holder');

        this.displayStorageOptions();

        return this;
    },

    addRepeatNode: function()
    {
        this.$el.find('ul.input-repeat-list').append( this.tplLocale({}));
    },

    displayStorageOptions: function()
    {
        var value = this.$storageSelector.val();

        this.$storageOptions.hide();
        this.$storageTestBtn.hide()

        var $fields = this.$storageOptions.filter('[data-storage="' + value + '"]');

        if( $fields.length )
        {
            $fields.show();
            // this.$storageTestBtn.show();
        }
    },

    getStorageOptions: function()
    {
        var method  = this.$storageSelector.val()
          , storage = {};

        this.$storageOptions.removeClass('error');

        if( method != '')
        {
            storage.method = method;

            this.$storageOptions.filter(':visible').find('input').each(function()
            {
                var field       = this.id.replace('storage.', '')
                  , value       = this.value;

                storage[ field ] = value;

            });
        }

        return storage;
    },

    displayStorageTest: function()
    {
        this.$storageTestBtn.find('p.help-block').html('');
        this.$storageTestBtn.show();
    },

    testStorageOptions: function()
    {
        var storage = this.getStorageOptions();

        if( storage )
        {
            var url     = $.Tapioca.config.apiUrl + this.appslug + '/library/test-storage'
              , $result = this.$storageTestBtn.find('p.help-block')
              , test    = $.ajax({
                                url:      url,
                                data:     JSON.stringify( { storage: storage } ),
                                dataType: 'json',
                                type:     'POST'
                            });
            
            $result.html('testing');

            test.done( function( p )
            {
                $result.html('settings ok');
            })

            test.fail( function( p )
            {
                var response =  $.parseJSON( p.responseText );

                $result.html( response.error );
            });
        }
    },

    submit: function( event )
    {
        var valid         = true,
            self          = this,
            whitelist     = $('#ext-whitelist').val(),
            locales       = [],
            defaultLocale = false;

        this.model.set('library.extwhitelist', whitelist.split(', '));

        $('#locales-form').find('input[name="locale-key"]').each(function( index )
        {
            var $this    = $(this),
                $parent  = $this.parents('li'),
                _label   = $parent.find('input[name="locale-label"]').val(),
                _default = $parent.find('input[name="locale-default"]').is(':checked'),
                _key     = $this.val();

            if( !_.isBlank( _label ) && !_.isBlank( _key ) )
            {
                var _obj = {
                    label: _label,
                    key:   _key
                };

                if( _default )
                {
                    defaultLocale = true;
                    _obj.default = true;
                }

                locales.push( _obj )
            }
        });

        if( locales.length > 0 )
        {
            if(!defaultLocale)
            {
                locales[0].default = true;
            }

            this.model.set('locales', locales);
        }
        else
        {
            valid = false;
        }

        var storage = this.getStorageOptions();

        if( storage )
        {
            this.model.set('storage', storage);
            this.$storageOptions.not(':visible').find('input').val('')
        }

        if(valid)
        {
            // Sets button state to loading - disables button and swaps text to loading text
            this.$btnSubmit.button('loading');

            this.model.save({}, {
                success: function(model, response)
                {
                    self.resetForm();
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