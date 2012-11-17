$.Tapioca.Models.Document = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute: '_ref',
    urlString:   'document',

    initialize: function( attributes, options )
    {
        this.appslug   = options.appslug;
        this.namespace = options.namespace;
        this.urlString = this.appslug + '/' + this.urlString + '/' + this.namespace;
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