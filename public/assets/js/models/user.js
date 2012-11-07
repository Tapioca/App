$.Tapioca.Models.User = $.Tapioca.Models.Tapioca.extend(
{
    urlString: 'user',

    confirmDelete: function()
    {
        var type = __('delete.user'),
            text = $.Tapioca.I18n.get('delete.question', this.get('name'), type),
            self = this;

        $.Tapioca.Dialog.confirm( _.bind( this.delete, this ), { text: text });
    },

    register: function()
    {
        return this.date( this.get('register') );
    }

});