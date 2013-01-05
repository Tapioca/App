
$.Tapioca.Views.AdminAppList = $.Tapioca.Views.Content.extend(
{
    viewpointer: [],

    render: function()
    {
        this.tplRow = Handlebars.compile( $.Tapioca.Tpl.admin.app['list-row'] );

        this.html( $.Tapioca.Tpl.admin.app.list );
        
        this.$table = this.$el.find('tbody');

        _.each( this.collection.models, this.display, this);

        return this;
    },

    display: function( model )
    {
        this.viewpointer[ model.cid ] = new $.Tapioca.Views.AdminAppListRow({
            model:       model,
            parent:      this.$table,
            tpl:         this.tplRow
        }).render();
    },

    onClose: function()
    {
        for( var i in this.viewpointer)
        {
            this.viewpointer[ i ].close();  
        }
    }
})