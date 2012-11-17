
$.Tapioca.Views.EmbedRef = Backbone.View.extend(
{
    className: 'embed-ref-list',

    initialize: function( options )
    {
        this.appslug   = $.Tapioca.appslug;
        this.abstracts = options.abstracts;
        this.namespace = this.model.get('namespace');
        this.$form     = options.form;

        var wHeight = $(window).height();
        var dHeight = wHeight * 0.8;

        $.Tapioca.Dialog.open({
            height: dHeight,
            width:  '80%'
        });

        this.$el.appendTo('#dialog-modal');

        this.render();
    },

    events: {
        'click a.btn': 'select'
    },

    select: function( event )
    {
        var ref = $( event.target ).attr('data-ref'),
            ret = this.abstracts.get( ref ).toJSON();

        this.$form.trigger('document::addDoc', ret);

        this.close()
    },

    render: function()
    {
        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection['embed-ref-list'] ),
            html = tpl({
                abstracts:  this.abstracts.toJSON(),
                name:       this.model.get('name'),
                digest:     this.model.get('digest')
            });

        this.$el.html( html );

        return this;
    },

    onClose: function()
    {
        $.Tapioca.Dialog.close();
    }

});