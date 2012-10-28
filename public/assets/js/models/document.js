$.Tapioca.Models.Document = $.Tapioca.Models.Tapioca.extend(
{
    id: '_ref',

    confirmDelete: function()
    {
        var type = __('delete.document');
        var text = $.Tapioca.I18n.get('delete.question', this.get('name'), type);
        console.log( text )
        console.log( this.deleteToken )
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