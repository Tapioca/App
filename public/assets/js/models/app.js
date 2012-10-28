$.Tapioca.Models.App = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute: 'slug',
    urlString: 'app',
    
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

        $.Tapioca.Dialog.open( _.bind( this.delete, this ), { text: text });
    },

    getDefaultLanguage: function()
    {
        return _.filter( this.get('locales'), function( locale )
        {
            if( locale.default )
                return true;
        }, this);
    }

});