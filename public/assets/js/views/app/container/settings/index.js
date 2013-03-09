
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
        'change #storage': 'displayStorageOptions'
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

        this.storageSelector = $('#storage');
        this.storageOptions  = $('#storage-data').find('div.control-group');

        this.displayStorageOptions();

        return this;
    },

    addRepeatNode: function()
    {
        this.$el.find('ul.input-repeat-list').append( this.tplLocale({}));
    },

    displayStorageOptions: function()
    {
        var value = this.storageSelector.val();

        this.storageOptions.hide();

        this.storageOptions.filter('[data-storage="' + value + '"]').show();
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

        // Storage
        var strotageMethod = this.storageSelector.val();

        if( strotageMethod != '')
        {
            var storage = {
                method: strotageMethod
            };

            this.storageOptions.filter(':visible').find('input').each(function()
            {
                var field = this.id.replace('storage.', '');

                storage[ field ] = this.value;

                if( this.value == '' )
                {
                    valid = false;
                }
            })

            if( valid )
                this.model.set('storage', storage);
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