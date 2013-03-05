$.Tapioca.Models.App = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute:   'slug',
    urlString:     'app',
    admins:        null,
    workingLocale: false,
    
    url: function()
    {
        var base = $.Tapioca.config.apiUrl + this.urlString;
        if (this.isNew()) return base;

        var base = $.Tapioca.config.apiUrl;
        var url = base + (base.charAt(base.length - 1) == '/' ? '' : '/') + this.id;

        if( this.deleteToken )
        {
            url += '?token=' + this.deleteToken.token;
            
            // reset deleteToken
            this.deleteToken = false;
        }

        return url;
    },

    confirmDelete: function()
    {
        var type = __('delete.app'),
            text = $.Tapioca.I18n.get('delete.question', this.get('name'), type),
            self = this;

        $.Tapioca.Dialog.confirm( _.bind( this.delete, this ), { text: text });
    },

    getWorkingLocale: function()
    {
        if( !this.workingLocale )
        {
            this.workingLocale = this.getDefaultLocale();
        }

        return this.workingLocale;
    },

    setWorkingLocale: function( key )
    {
        var locale = _.filter( this.get('locales'), function( locale )
        {
            if( locale.key == key )
            {
                this.workingLocale = locale;
                return true;
            }
        }, this);

        return locale[0];
    },

    getDefaultLocale: function()
    {
        var locale = _.filter( this.get('locales'), function( locale )
        {
            if( locale.default )
                return true;
        }, this);

        return locale[0];
    },

    isAppAdmin: function()
    {
        if( _.isNull( this.admins ) )
        {
            var admins = _.filter( this.get('team'), function( member )
            {
                return (member.role == 'admin');
            })

            this.admins = _.pluck( admins, 'id' );
        }
        
        return _.contains( this.admins, $.Tapioca.Session.get('id') );
    },

    getExtWhitelist: function()
    {
        if( _.isUndefined( this.get('extwhitelist') ) )
        {
            this.set('extwhitelist', $.Tapioca.config.medias.extWhitelist);
        }

        return this.get('extwhitelist');
    },

    getStorage: function()
    {
        if( _.isUndefined( this.get('storage') ) )
        {
            this.set('storage', { method: ''});
        }

        return this.get('storage');
    }

});