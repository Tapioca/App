
$.Tapioca.Views.AppIndex = $.Tapioca.Views.Content.extend(
{
	
	
    render: function()
    {
        this.$el.appendTo('#app-content');

        // var model = this.model.toJSON()

        // var tpl  = Handlebars.compile( $.Tapioca.Tpl.admin.index ),
        //     html = tpl( model );

        this.html( $.Tapioca.Tpl.app.container.home.index );

        return this;
    }
});