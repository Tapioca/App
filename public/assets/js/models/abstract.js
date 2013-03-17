$.Tapioca.Models.Abstract = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute: '_ref',

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
        var type = __('delete.collection'),
            text = $.Tapioca.I18n.get('delete.question', this.get('_ref'), type);

        $.Tapioca.Dialog.confirm( _.bind( this.delete, this ),  _.bind( this.clearDelete, this ), { text: text });
    }
});