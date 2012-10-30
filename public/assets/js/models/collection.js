$.Tapioca.Models.Collection = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute: 'namespace',
    urlString: 'collection',
    
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
        var type = __('delete.collection'),
            text = $.Tapioca.I18n.get('delete.question', this.get('name'), type),
            self = this;

        $.Tapioca.Dialog.open( _.bind( this.delete, this ), { text: text });
    }
});