
$.Tapioca.Views.NavApp = Backbone.View.extend(
{
    className:    'app-nav app-nav-active',
    tagName:      'div',
    viewspointer: [],

    initialize: function( options )
    {
        this.tplRow      = options.tplRow;
        this.appslug     = this.model.get('slug');

        this.$el.appendTo( options.parent );

        var _channel = this.appslug + 'section::highlight';
        $.Tapioca.Mediator.subscribe( _channel, _.bind( this.highlight,  this ) );
    },

    render: function()
    {
        var model = this.model.toJSON(),
            tpl   = Handlebars.compile( $.Tapioca.Tpl.app.nav.app ),
            cols  = $.Tapioca.UserApps[ this.appslug ].collections;

        model.isAppAdmin = this.model.isAppAdmin();
    
        this.$el.html( tpl( model ));

        if( cols.length > 0)
        {
            this.$el.find('li.app-nav-collections-empty').remove();
        }

        this.$list = $('#app-nav-collections-' + this.appslug );

        _.each( $.Tapioca.UserApps[ this.appslug ].collections.models, this.displayCollection, this);

        this.$navLinks = this.$el.find('li a');

        return this;
    },

    displayCollection: function( model )
    {
        this.viewspointer[ model.cid ] = new $.Tapioca.Views.NavAppCollection({
            tpl:     this.tplRow,
            appslug: this.appslug,
            model:   model,
            parent:  this.$list
        }).render();
    },

    highlight: function()
    {
        var href = document.location.href;
        
        this.$navLinks.each(function()
        {
            if( href.indexOf( this.href ) == 0 )
            {
                $(this).addClass('active');
                // return;
            }
            else
            {
                $(this).removeClass('active');
            }
        })
    },

    onClose: function()
    {
        _.each( this.viewspointer, function( view )
        {
            view.close();
        })
    }
});