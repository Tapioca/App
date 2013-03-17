
$.Tapioca.Models.Tapioca = Backbone.NestedModel.extend({

    deleteToken: false,

    url: function()
    {
        var base = $.Tapioca.config.apiUrl + this.urlString;
        if (this.isNew()) return base;
        var url = base + (base.charAt(base.length - 1) == '/' ? '' : '/') + this.id;

        if( this.deleteToken )
        {
            url += '?token=' + this.deleteToken.token;
            
            // reset deleteToken
            this.deleteToken = false;
        }

        return url;
    },

    date: function( obj )
    {
        return obj.sec;
    },

    // request a delete token to the server first
    delete: function()
    {
        if( !this.deleteToken )
        {
            var self = this;

            // get delete token from API
            $.ajax({
                url:      this.url(),
                dataType: 'json',
                type:     'DELETE',
                success: function( response )
                {
                    self.deleteToken = response;
                    self.confirmDelete();
                },
                error: function()
                {}
            })
        }
        else
        {
            this.destroy();
        }
    },

    clearDelete: function()
    {
        this.deleteToken = false;
    },

    // Empty function by default. 
    // Override it with model own logic.
    confirmDelete: function(){}
});