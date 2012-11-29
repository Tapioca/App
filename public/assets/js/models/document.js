$.Tapioca.Models.Document = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute: '_ref',
    urlString:   'document',

    initialize: function( attributes, options )
    {
        this.appslug   = options.appslug;
        this.namespace = options.namespace;
        this.urlString = this.appslug + '/' + this.urlString + '/' + this.namespace;
        this.locale    = options.locale;
    },

    appendLocale: function()
    {
        return '?l=' + this.locale;
    },

    url: function()
    {
        var base = $.Tapioca.config.apiUrl + this.urlString;

        if (this.isNew())
        {
            return base + this.appendLocale();
        }

        var url = base + (base.charAt(base.length - 1) == '/' ? '' : '/') + this.id;

        if( this.deleteToken )
        {
            url += '?token=' + this.deleteToken.token;
            
            // reset deleteToken
            this.deleteToken = false;

            return url;
        }

        return url + this.appendLocale();
    },

    // to delete ?
    confirmDelete: function()
    {
        var type = __('delete.document');
        var text = $.Tapioca.I18n.get('delete.question', this.get('name'), type);
        console.log( text )
        console.log( this.deleteToken )
    }

});