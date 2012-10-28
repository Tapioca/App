$.Tapioca.Models.App = $.Tapioca.Models.Tapioca.extend(
{
    id: 'slug',
    
    urlString: function()
    {
        return this.get('slug');
    },

    confirmDelete: function()
    {
        var type = __('delete.app');
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