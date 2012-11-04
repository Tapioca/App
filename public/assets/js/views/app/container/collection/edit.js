
$.Tapioca.Views.CollectionEdit = $.Tapioca.Views.FormView.extend(
{
    initialize: function( options )
    {
        this.isNew = options.isNew;

        this.$el.appendTo('#app-content');
        
        return this;
    },

    events: _.extend({
        'keyup #namespace': 'slugify',
        'keyup #name':      'slugify'
    }, $.Tapioca.Views.FormView.prototype.events),


    slugify: function( event )
    {
        if( this.isNew && event.target.value != '')
        {
            this.$namespace.val( $.Tapioca.Components.Form.slugify( event.target.value ) );
        }
    },

    render: function()
    {
        var model       = this.model.toJSON();

        model.isNew     = this.isNew;
        model.pageTitle = ( this.isNew ) ?
                            __('title.new_collection') :
                            $.Tapioca.I18n.get('title.edit_collection', this.model.get('name'));

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.edit ),
            html = tpl( model );

        this.html( html, 'app-form');

        this.$namespace = $('#namespace');

        this.editor();

        return this;
    },

    editor: function()
    {
        var $form = $('#form'),
            dropCount = 0,
            sortableOption = {
              connectWith: '#form ul.sortable',
              placeholder: 'ui-placeholder',
              handle:      'span.element-required',
              items:       'li[data-type]',
              receive: function(event, ui)
              {
                  dropCount = ++dropCount;

                  if(dropCount>1)
                  {
                    $(ui.item).addClass('ui-deleteMe');
                  }
              },
              start: function(event, ui)
              {
                  dropCount = 0;   
                  
                  var placeholders = 0;

                  $form.find('li.ui-placeholder').each(function()
                  {
                    placeholders = ++placeholders;

                    if(placeholders > 1)
                    {
                      $(this).hide();
                    }
                  });
              },
              stop: function(event, ui)
              {
                var $item = $(ui.item);

                if($item.attr('data-node'))
                {
                  $item.find('ul.sortable').sortable(sortableOption);
                }

                $form.find('li.ui-deleteMe').remove();
                $form.find('ul.sortable').removeClass('empty');
                dropCount = 0;

                $item.find('input[name="label"]').focus();
              }
            };

        $('#form-elements').find('li').draggable({
            helper: 'clone',
            connectToSortable: '#form ul.sortable'
        });

        $('#form').find('ul.sortable').sortable(sortableOption);

        // EVENTS

        $form.on('blur', 'input[name="label"]', function()
        {
          if(this.value!='')
          {
            var $wrapper = $(this).next('span.input-id-wrapper'),
                $input   = $wrapper.find('input[name="id"]'),
                _str     = $.Tapioca.Components.Form.slugify(this.value),
                _width   = $.Tapioca.Components.Form.fieldWidth(this.parentNode, this.value);

            this.style.width = _width + 'px';

            $wrapper.show();
            $input.val( _str ).width( _width );
            this.parentNode.className += ' defined';
          }
        });

        $form.on('keyup', 'input[name="label"], input[name="id"]', this.onEnter);

        $form.on('click', 'input[name="id"]', function()
        {
          $(this).select();
        });

        $form.on('keyup', 'input[name="id"]', function()
        {
          if(this.value!='')
          {
            this.value = $.Tapioca.Components.Form.slugify(this.value);
            var _width = $.Tapioca.Components.Form.fieldWidth(this.parentNode, this.value);

            this.style.width = _width + 'px';
          }
        });

        $form.on('click', 'span.remove', function()
        {
          $(this).parents('li').eq(0).remove();

          if($form.find('li').size() == 0)
          {
            $form.find('ul').addClass('empty');
          }
        });

        $form.on('click', 'span.options-trigger', function()
        {
          $(this).parents('li').eq(0).find('div.element-options').toggle();
        })

        $('#submit').click(function()
        {
          $('#form').find('li').removeClass('warning');

          summary = [];

          var schema  = parseForm( $('#form > ul > li[data-type]'), '' );
          
          var json = JSON.stringify(schema, null, "    ");
          
          $('#schema-holder').val(json);
          
          var json = JSON.stringify(summary, null, "    ");

          $('#summary-holder').val(json);
        });
    },

    submit: function()
    {

    },

    onClose: function()
    {
        $('#form').off();
    }
});