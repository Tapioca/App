$.Tapioca.Models.Search = $.Tapioca.Models.Tapioca.extend(
{
    idAttribute: '_ref',

    url: function()
    {
        return this.collection.url() + '/' + this.id;
    }
});