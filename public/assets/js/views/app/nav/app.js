
$.Tapioca.Views.NavApp = Backbone.View.extend(
{
    className:    'app-nav app-nav-active',
    tagName:      'div',
    viewpointer: [],

    initialize: function( options )
    {
        this.tplRow      = options.tplRow;
        this.appslug     = this.model.get('slug');

        this.$el.appendTo( options.parent );

        var _channel = this.appslug + '::section::highlight';
        $.Tapioca.Mediator.subscribe( _channel, _.bind( this.highlight,  this ) );
    },

    events: {
        'click a.upload-trigger': 'openUpload'
    },

    render: function()
    {
        var model = this.model.toJSON(),
            tpl   = Handlebars.compile( $.Tapioca.Tpl.app.nav.app );

        this.cols  = $.Tapioca.UserApps[ this.appslug ].collections;

        this.cols.bind('add', this.displayCollection, this);

        model.isAppAdmin = this.model.isAppAdmin();
    
        this.$el.html( tpl( model ));

        if( this.cols.length )
        {
            this.$el.find('li.app-nav-collections-empty').remove();
        }

        this.$list = $('#app-nav-collections-' + this.appslug );

        _.each( this.cols.models, this.displayCollection, this);

        this.$navLinks = this.$el.find('li a');

        return this;
    },

    displayCollection: function( model )
    {
        this.viewpointer[ model.cid ] = new $.Tapioca.Views.NavAppCollection({
            tpl:     this.tplRow,
            appslug: this.appslug,
            model:   model,
            parent:  this.$list
        }).render();
    },

    highlight: function()
    {
        var href = document.location.href;
        
        _.all(this.$navLinks, function( link )
        {
            if( href.indexOf( link.href ) == 0 )
            {
                link.className = 'active';
                return false;
            }

            return true;
        })
    },

    openUpload: function()
    {
        $.Tapioca.FileUpload.init({
            appslug:           this.appslug,
            singleFileUploads: false,
        });
    },

    onClose: function()
    {
        this.cols.unbind('add', this.displayCollection);

        for( var i in this.viewpointer)
        {
            this.viewpointer[ i ].close();  
        }
    }
});