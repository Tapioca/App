$.Tapioca.Views.AdminIndex = $.Tapioca.Views.Content.extend(
{
    render: function()
    {
        this.$el.appendTo('#app-content');

        // var tpl  = Handlebars.compile( $.Tapioca.Tpl.admin.index ),
        //     self = this,
        //     html = tpl( this.model.toJSON() );

        this.html( $.Tapioca.Tpl.admin.index );

        return this;
    }
})