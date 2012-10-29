
$.Tapioca.Views.AdminUserList = $.Tapioca.Views.Content.extend(
{
    viewPointers: [],

    render: function()
    {
        this.tplRow = Handlebars.compile( $.Tapioca.Tpl.admin.user['list-row'] );

        this.html( $.Tapioca.Tpl.admin.user.list );
        
        this.$table = this.$el.find('tbody');

        // do not display current user
        var userId = $.Tapioca.Session.get('id'),
            users  = _.filter( this.collection.models, function(user)
            {
                return (user.get('id') != userId)
            }, this);

        _.each( users, this.display, this);

        return this;
    },

    display: function( model )
    {
        this.viewPointers[ model.cid ] = new $.Tapioca.Views.AdminUserListRow({
            model:       model,
            parent:      this.$table,
            tpl:         this.tplRow
        }).render();
    },

    onClose: function()
    {
        _.each(this.viewPointers, function( view )
        {
            view.close();
        }, this);
    }
})