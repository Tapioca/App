
$.Tapioca.Views.AdminUserList = $.Tapioca.Views.Content.extend(
{
    viewpointer: [],

    render: function()
    {
        this.tplRow   = Handlebars.compile( $.Tapioca.Tpl.admin.user['list-row'] );
        this.isMaster = $.Tapioca.Session.isMaster();

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
        this.viewpointer[ model.cid ] = new $.Tapioca.Views.AdminUserListRow({
            model:       model,
            isMaster:    this.isMaster,
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