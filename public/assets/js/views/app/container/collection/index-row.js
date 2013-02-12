
$.Tapioca.Views.CollectionRow = Backbone.View.extend(
{
    tagName: 'tr',

    initialize: function( options )
    {
        this.appslug      = options.appslug;
        this.namespace    = options.namespace;
        this.tpl          = options.tpl;
        this.locale       = options.locale;
        this.digestSchema = options.digestSchema;

        this.$el.appendTo( options.parent );

        this.model.bind('change', this.render, this);
        this.model.bind('destroy', this.close, this);
        
        this.render();

        return this;
    },

    events: {
        'click ul.dropdown-menu a':   'setStatus',
        'click a.btn-delete-trigger': 'delete'
    },

    render: function()
    {
        var model = this.model.toJSON(),
            args  = [ this.appslug, this.namespace, model._ref ];

        model.appslug      = this.appslug;
        model.namespace    = this.namespace;
        model.locale       = this.locale;
        model.isAppAdmin   = $.Tapioca.Session.isAdmin();
        model.uri          = $.Tapioca.app.setRoute('appCollectionRef', args);
        model.digestSchema = this.digestSchema;

        this.$el.html( this.tpl( model ) );

        return this;
    },

    setStatus: function( event )
    {
        var _status = parseInt( $( event.target ).attr('data-status')),
            _url    = this.model.url() + '?l=' + this.locale.key, 
            _self   = this;

        var put = $.ajax({
            url:      _url,
            data:     JSON.stringify({status: _status}),
            dataType: 'json',
            type:     'PUT'
        });

        put.done( function( p )
        {
            _self.model.set( p );
        });

        put.fail( this.error );
    },

    delete: function()
    {
        this.model.delete();
    },

    error: function( p )
    {
        var response = $.parseJSON( p.responseText )
        alert( response.error )
    },

    onClose: function()
    {
        this.model.unbind('destroy', this.close);
        this.model.unbind('change', this.render);
    }
});