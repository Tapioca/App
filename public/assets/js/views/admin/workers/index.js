
$.Tapioca.Views.AdminWorkers = $.Tapioca.Views.Content.extend(
{
    render: function()
    {
        this.tplRow = Handlebars.compile( $.Tapioca.Tpl.admin.workers.row );

        this.html( $.Tapioca.Tpl.admin.workers.list );
        
        this.$table = this.$el.find('tbody');

        return this;
    },

    onClose: function()
    {
        _.each(this.viewPointers, function( view )
        {
            view.close();
        }, this);
    }
})