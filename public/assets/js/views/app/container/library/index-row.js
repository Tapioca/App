
$.Tapioca.Views.LibraryRow = Backbone.View.extend(
{
    tagName: 'tr',
    tag:      true,
    category: true,
    
    initialize: function( options )
    {
        this.appslug    = options.appslug;
        this.tpl        = options.tpl;
        this.mergedTags = options.mergedTags;
        this.$form      = options.form;

        this.$el.appendTo( options.parent );

        this.model.bind('destroy', this.close, this);
        
        this.render();

        return this;
    },

    events: {
        'click a.btn-delete-trigger':  'delete',
        'click a.select-file-trigger': 'select'
    },

    render: function()
    {
        var model = this.model.toJSON();

        model.appslug    = this.appslug;
        model.isAppAdmin = $.Tapioca.Session.isAdmin();

        this.$el.html( this.tpl( model ) );

        return this;
    },

    filterTags: function( tag )
    {
        if(tag == 'all')
        {
            this.$el.show();
            this.tag = true;
            return;
        }

        if(this.category)
        {
            if($.inArray(tag, this.mergedTags) == -1)
            {
                this.tag = false;
                this.$el.hide();
                return;
            }
            this.tag = true;
            this.$el.show();
        }
    },

    filterCategory: function( category )
    {
        if(category == 'all')
        {
            this.$el.show();
            this.category = true;
            return;
        }

        if(this.tag)
        {
            if(this.model.get('category') != category)
            {
                this.category = false;
                this.$el.hide();
                return;
            }

            this.category = true;
            this.$el.show();
        }
    },

    select: function()
    {
        var ret = {
            filename: this.model.get('filename'),
            category: this.model.get('category')
        }

        // embed image size 
        if( ret.category === 'image' )
        {
            ret.size = this.model.get('size');
        }

        this.$form.trigger('document:addFile', ret);

        // $.Tapioca.Dialog.close();
    },

    delete: function()
    {
        this.model.delete();
    },

    onClose: function()
    {
        this.model.unbind('destroy', this.close);
    }
});