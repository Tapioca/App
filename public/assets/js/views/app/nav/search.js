
$.Tapioca.Views.SearchForm = Backbone.View.extend(
{
    tagName: 'p',
    query: '',
    displayResult: false,

    initialize: function()
    {
        this.$el.appendTo('#search-form');

        $.Tapioca.Mediator.subscribe('search::active',   _.bind( this.active,   this ) );
        $.Tapioca.Mediator.subscribe('search::disabled', _.bind( this.disabled, this ) );

        this.render();
    },

    events: {
        'keyup :input' : 'change'
    },

    render: function()
    {
        this.$el.html( $.Tapioca.Tpl.app.nav['search-form'] );

        this.$input = document.getElementById('search-query');

        return this;
    },

    change: function( event )
    {
        if( !_.isUndefined( event ) )
        {
            // Do not trigger `change`on cursor move
            if( $.inArray( event.keyCode, [37, 38, 39, 40] ) !== -1)
                return;            
        }

        var query = this.$input.value;

        if( query.length < 2 )
            return;

        if( query == this.query )
            return;

        this.query = query;

        if( !this.displayResult )
        {
            var href = $.Tapioca.app.setRoute('appSearchResult', [ $.Tapioca.appslug ])

            Backbone.history.navigate( href, true );

            this.displayResult = true;
        }

        $.Tapioca.Mediator.publish('search::query', this.query);
    },

    active: function()
    {
        this.query           = '';
        this.$input.disabled = false;
    },

    disabled: function()
    {
        this.query           = '';
        this.displayResult   = false;
        this.$input.disabled = true;
    }

});