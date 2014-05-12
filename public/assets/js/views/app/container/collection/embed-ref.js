
$.Tapioca.Views.EmbedRef = Backbone.View.extend(
{
    className: 'embed-ref-list',

    initialize: function( options )
    {
        this.appslug   = $.Tapioca.appslug;
        this.abstracts = options.abstracts;
        this.locale    = options.locale;
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
        'click a.btn': 'select',
        'click #close-popup-list': 'closeDialog'
    },

    closeDialog: function()
    {
        $.Tapioca.Dialog.close()
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
        var abstracts = this.abstracts.toJSON(),
            locale    = this.locale.key,
            models    = _.filter( abstracts, function( model)
                        {
                            // if document not translanted in current locale
                            if( _.isUndefined( model.revisions.active[ locale ] ) )
                                return false;

                            // is the active revision published ?
                            var active   = ( model.revisions.active[ locale ] - 1),
                                revision = model.revisions.list[ active ];

                            return (revision.status == 100)

                        });

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection['embed-ref-list'] ),
            html = tpl({
                abstracts:  models,
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