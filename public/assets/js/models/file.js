$.Tapioca.Models.File = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute: 'filename',
    
    url: function()
    {
        var base = this.collection.url();
        if (this.isNew()) return base;

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
        var type = __('delete.file'),
            text = $.Tapioca.I18n.get('delete.question', this.get('filename'), type),
            self = this;

        $.Tapioca.Dialog.open( _.bind( this.delete, this ), { text: text });
    }
});