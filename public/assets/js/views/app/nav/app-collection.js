
$.Tapioca.Views.NavAppCollection = Backbone.View.extend(
{
    tagName: 'li',

    initialize: function( options )
    {
        this.tpl     = options.tpl;
        this.appslug = options.appslug;

        this.$el.prependTo( options.parent );

        this.model.bind('change',  this.render, this);
        this.model.bind('destroy', this.close, this);

        return this;
    },

    render: function(eventName)
    {

        var namespace = this.model.get('namespace'),
            _html     = this.tpl({
                appslug:    this.appslug,
                namespace : namespace,
                name:       this.model.get('name')
            });
        
        this.$el.html( _html ).attr('data-namespace', namespace );
        
        return this;
    },

    onClose: function()
    {
        this.model.unbind('change', this.render);
        this.model.unbind('destroy', this.close);
    }
});