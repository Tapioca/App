
$.Tapioca.Views.AdminWorkers = $.Tapioca.Views.Content.extend(
{
    render: function()
    {
        this.tplRow = Handlebars.compile( $.Tapioca.Tpl.admin.workers.row );

        this.html( $.Tapioca.Tpl.admin.workers.list );
        
        this.$table = this.$el.find('tbody');

        this.get();

        return this;
    },

    get: function()
    {
        var url  = $.Tapioca.config.apiUrl + 'job',
            self = this,
            hxr  = $.ajax({
                url:      url,
                dataType: 'json',
                success: function(data)
                {
                    var r    = data.results,
                        html = '';

                    for( var i = -1; ++i < data.total;)
                    {
                        html += self.tplRow( r[ i ] );
                    }

                    self.$table.html( html );
                }
            });
    },

    display: function( jobs )
    {

    },

    onClose: function()
    {
        for( var i in this.viewpointer)
        {
            this.viewpointer[ i ].close();  
        }
    }
})