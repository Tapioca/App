
$.Tapioca.Views.DocForm = Backbone.View.extend(
{
    className:  'document-form',
    initialized: false,           // prevent this.change() on page init
    counters:    {},              // object that keep the count ok increment for loops

    initialize: function( options )
    {
        this.$el.appendTo('#form-holder');

        this.appslug   = options.appslug;
        this.baseUri   = options.baseUri;
        this.locale    = options.locale;
        this.namespace = options.schema.get('namespace');
        this.schema    = options.schema.get('schema');
        this.parent    = options.parent;
        this.factory   = new formFactory( this.locale.key );

        this.tplEmbedRef = Handlebars.compile( $.Tapioca.Tpl.app.container.collection['embed-ref'] );
        this.tplThumb    = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.thumb );

        // Handlebars Helpers

        var self = this;

        Handlebars.registerHelper('_incCounter', function(options)
        {
            var counter = options.hash.counter;

            if (!self.counters[counter])
            {
                self.counters[counter] = 0;
            }

            ++self.counters[counter];
        });

        Handlebars.registerHelper('_getCounter', function(options)
        {
            return self.counters[options.hash.counter];
        });

        Handlebars.registerHelper('_embedData', function(context, options)
        {
            // NESTED HELPERS NOT ALLOWED 
            // https://github.com/wycats/handlebars.js/issues/222
            var prefix   = options.hash.prefix;
                prefix   = prefix.replace(/_#/g, '[{{').replace(/#_/g, '}}]').replace(/II/g, '"'),
                template = Handlebars.compile(prefix);

            prefix = template({})

            return self.embedData(context, '', prefix);
        });

        Handlebars.registerHelper('_embedDoc', function(context, options)
        {
            if( !_.isUndefined( context ) )
            {
                // NESTED HELPERS NOT ALLOWED 
                // https://github.com/wycats/handlebars.js/issues/222
                var prefix   = options.hash.prefix;
                    prefix   = prefix.replace(/_#/g, '[{{').replace(/#_/g, '}}]').replace(/II/g, '"'),
                    template = Handlebars.compile(prefix);

                prefix = template({});

                var abstracts = $.Tapioca.UserApps[ self.appslug ].data[ options.hash.collection ].abstracts,
                    abstract  = abstracts.get( context.ref );

                return self.docPreview(context, abstract.get('digest'), prefix);
            }
        });

        this.factory.walk( this.schema, '', '');

        this.getDependencies();

        return this;
    },

    events:
    {
        'click a.array-repeat-trigger'                                          : 'addNode',
        'click ul.input-repeat-list li:last-child a.input-repeat-trigger'       : 'addInput',
        'click ul.input-repeat-list li:not(:last-child) a.input-repeat-trigger' : 'removeInput',
        'click a.doc-list-trigger'                                              : 'docList',
        'click a.doc-remove-trigger'                                            : 'docRemove',
        'click a.file-list-trigger'                                             : 'fileList',
        'click a.file-remove-trigger'                                           : 'fileRemove',
        'click a.btn-upload-trigger'                                            : 'upload',
        'document:addFile'                                                      : 'addFile',
        'document::addDoc'                                                      : 'addDoc'
    },

    change: function()
    {
        this.parent.change();
    },

    isDependenciesLoaded: function()
    {
        if( this.loaded == this.total )
            this.render();
    },

    getDependencies: function()
    {
        var dependencies = this.factory.getDependencies(),
            self          = this,
            callback      = function()
            {
                ++self.loaded;
                self.isDependenciesLoaded();
            };

        this.total       = dependencies.length;
        this.loaded      = 0;

        if( this.total )
        {
            for( var i = -1; ++i < this.total; )
            {
                if( dependencies[ i ] === '__library__' )
                {
                    var library = $.Tapioca.UserApps[ this.appslug ].library;

                    if( !library.isFetched() )
                    {
                        library.fetch({
                            success: callback
                        });
                    }
                    else
                        callback()
                }
                else
                {
                    var collection = $.Tapioca.UserApps[ this.appslug ].data[ dependencies[ i ] ].abstracts;

                    if( !collection.isFetched() )
                        collection.fetch({
                            success: callback
                        });
                    else
                        callback();
                }
            } // for
        }
        else
        {
            this.render();
        }
    },

    render: function()
    {
        var form     = this.factory.getHtml(),
            template = Handlebars.compile( form );
            _html    = template({
                            model: this.model.toJSON()
                        });

        this.$el.html( _html );

        this.bindInput();

        this.initialized = true;

        return this;
    },

    bindInput: function()
    {
        var self    = this,
            _parent = this.parent;

        this.$el.find('table[data-dbref=true]').each(function()
        {
            var $parent = $(this).prev('div.btn-group').eq(0).hide();
        });

        this.$el.find('textarea[data-wysiwyg]').not('[data-binded="true"]').each(function()
        {
            var $this    = $(this),

                buttons  = ($this.attr('data-toolbar')) ? 
                            $this.attr('data-toolbar').split('::') : 
                            ['html', '|', 'bold', 'italic', 'link'],

                settings = {
                    buttons:       buttons,
                    keyupCallback: _.bind( _parent.change, _parent),
                    paragraphy:    false,
                    minHeight:     100
                };
//console.log(settings)
            $this
                .redactor(settings)
                .attr('data-binded', 'true');
        });

        this.$el.find('input.input-date').not('[data-binded="true"]').each(function()
        {
            var $this     = $(this),
                $altField = $('input[name="'+$this.attr('data-name')+'"]');
            
            $this.attr('data-binded', 'true');
            
            $this.datepicker({
                dateFormat: 'dd/mm/yy',
                onSelect: function(dateText, inst)
                {
                    var _getDate    = $this.datepicker('getDate'),
                        epoch       = $.datepicker.formatDate('@', _getDate),
                        defaultDate = $.datepicker.formatDate('MM d, yy', _getDate);

                    $.datepicker.setDefaults( {
                        defaultDate: new Date(defaultDate)
                    });

                    $altField.val(epoch / 1000);

                    _parent.change();
                }
            });
        });

        this.$el.find(':input[data-rules]').not('[data-ruled="true"]').each(function()
        {
            var $this = $(this),
                _rule = {
                    name:    $this.attr('name'),
                    display: $this.attr('data-label'),
                    rules:   $this.attr('data-rules')
                };

            _parent.addRules( [_rule] );   

            $this.attr('data-ruled', 'true');
        });
    },

    targetData: function(event)
    {
        var $target = $(event.target);

        // prevent click on icon
        if(!$target.hasClass('btn'))
        {
            $target = $target.parents('a.btn');
        }

        var prefix      = $target.attr('data-prefix'),
            key         = $target.attr('data-key'),
            node        = this.getDescendantProp(key),
            isArray     = (node.type == 'array'),
            prefixTmp   = (node.id == prefix) ? '' : prefix,
            prefixCheck = prefixTmp.split('.');

        if(prefixCheck.length > 1)
        {
            var lastIndex = (prefixCheck.length - 1),
                lastValue = prefixCheck[lastIndex].replace('[]', '');

            if(lastValue == node.id)
            {
                prefixCheck.splice(lastIndex, 1);
                prefixTmp = prefixCheck.join('.');
            } 
        }

        prefix = this.factory.prefix('', prefixTmp, node.id, isArray);

        return {
            $: $target,
            node: node,
            prefix: prefix,
            key: key
        }
    },

    getDescendantProp: function(key)
    {
        var keys = key.split('.'),
            ret  = this.schema,
            last = (keys.length - 1);

        for(var i = -1, total = keys.length; ++i < total;)
        {
            ret = ret[keys[i]];
            if((ret.type == 'array' || ret.type == 'object') && i != last)
            {
                ret = ret.node;
            }
        }

        // extend to avoid to modify original object
        ret = $.extend({}, ret);

        return ret;
    },

    /* External Document */

    docPreview: function(data, digest, prefix)
    {
        return this.tplEmbedRef({
            prefix: prefix,
            digest: digest,
            fields: {
                str: this.embedData(data, '', prefix)
            }
        });
    },

    addDoc: function(event, doc)
    {
        var target   = this.targetData(this.target),
            _html    = '',
            ret      = {ref: doc._ref};

        if( !_.isUndefined( this.docEmbedded ) )
        {
            var url   = $.Tapioca.config.apiUrl + this.appslug + '/document/' + this.docCollection + '/' + doc._ref+'?l='+this.locale.key,
                query = {select: this.docEmbedded.split('::')},
                self  = this;

            url = url+'&q='+JSON.stringify(query);

            hxr = $.ajax({
                url: url,
                dataType: 'json',
                async: false,
                success: function(data)
                {
                    delete data._id;

                    ret.embedded = data;
                }
            });
        }

        _html = this.docPreview(ret, doc.digest, target.prefix);

        var $parent = target.$.parents('div.btn-group').eq(0);

        $parent.after(_html);
        $parent.hide();
    },

    docRemove: function(event)
    {
        var $target = $( event.currentTarget )
          , prefix  = $target.attr('data-prefix')
          , $parent = $target.parents('table').eq(0)
//           , $inputs = $parent.find('input')

//         for( var i = -1, l = $inputs.length; ++i < l; )
//         {
//             if( $inputs[ i ].name.substr(-3) == 'ref' )
//             {
//                 console.log($inputs[ i ].value)
//                 continue
//             }
//         }

// console.log( $inputs )

        if( !_.isUndefined( this.model.get( prefix ) ) )
        {
            this.model.unset( prefix )
        }

        $parent.prev('div.btn-group').eq(0).show();
        $parent.remove();

        this.change();
    },

    docList: function(event)
    {
        this.target    = event;
        var target     = this.targetData(this.target);

        this.docCollection = target.$.attr('data-collection');
        this.docEmbedded   = target.$.attr('data-embedded');

        var collection = $.Tapioca.UserApps[ this.appslug ].collections.get( this.docCollection ),
            abstracts  = $.Tapioca.UserApps[ this.appslug ].data[ this.docCollection ].abstracts;
        
        new $.Tapioca.Views.EmbedRef({
            model:     collection,
            abstracts: abstracts,
            locale:    this.locale,
            form:      this.$el
        });
    },

    /* Library */

    fileList: function(event)
    {
        this.target = event;

        new $.Tapioca.Views.EmbedFile({
            collection: $.Tapioca.UserApps[ this.appslug ].library,
            form:       this.$el
        });
    },

    addFile: function(event, file)
    {
        var target   = this.targetData(this.target),
            _html    = '',
            hasThumb = target.$.parents('div.controls').eq(0).find('ul.thumbnails');

        if(hasThumb.size() > 0)
        {
            hasThumb.remove();
        }

        _html = this.embedData(file, _html, target.prefix);

        var $parent = target.$.parents('div.btn-group').eq(0);

        $parent.before(_html);

        $.Tapioca.Dialog.close();
    },

    fileRemove: function(event)
    {
        var $target = $( event.currentTarget )
          , prefix  = $target.attr('data-prefix')

        if( !_.isUndefined( this.model.get( prefix ) ) )
        {
            this.model.unset( prefix )
        }

        $target.parents('ul.thumbnails').remove();

        this.change();
    },

    upload: function( event )
    {
        this.target = event;

        $.Tapioca.FileUpload.init({
                appslug:           this.appslug,
                singleFileUploads: true,
            },
            _.bind( this.addFile, this )
        );
    },

    /* Nodes Manager */

    addInput: function(event)
    {
        var target      = this.targetData(event),
            type        = this.factory.getType(target.node.type),
            prefixCheck = target.prefix.split('.');
            lastIndex   = (prefixCheck.length - 1);

        prefixCheck.splice(lastIndex, 1);
        
        target.prefix       = prefixCheck.join('.');
        target.node.pattern = true;

        this.factory.define(type, target.node, target.prefix, target.key);

        var htmlStr  = this.factory.getHtml(),
            template = Handlebars.compile(htmlStr),
            html     = template({});
        
        target.$.parents('ul.input-repeat-list').append(html);

        this.bindInput();
    },

    removeInput: function(event)
    {
        $(event.target).parents('li').remove();
    },

    addNode: function(event)
    {
        var target = this.targetData(event);

        this.factory.define('loopStart', target.node);
        this.factory.define('incCounter', target.node);

        if(_.isUndefined(target.node.template))
        {
            this.factory.walk(target.node.node, target.prefix, target.key);
        }
        else
        {
            this.factory.define('template', target.node.template, null, null);
        }

        this.factory.define('loopEnd', null);

        var htmlStr  = this.factory.getHtml(),
            template = Handlebars.compile(htmlStr),
            html     = template({});
        
        target.$.parents('p.align-right').before(html);

        this.bindInput();
    },

    /* Embedded Data */

    embedData: function(hash, str, prefix)
    {

        var iterator = 0;

        for(var i in hash)
        {
            var prefixTmp = prefix + '.' + i;

            if(_.isString(hash[i]) || _.isNumber(hash[i]) || _.isNull(hash[i]))
            {
                str += '<input type="hidden" name="' + prefixTmp + '" value="' + hash[i] + '">';
            }
            else
            {
                if(_.isArray(hash[i]))
                {
                    if(!_.isString(hash[i][0]))
                    {
                        for(var j = -1, total = hash[i].length; ++j < total;)
                        {
                            var p = prefixTmp + '[' + j + ']';

                            str += this.embedData(hash[i][j], '', p);
                        }
                    }
                    else
                    {
                        var p = prefixTmp + '[' + j + '][]';
                        
                        for(var j = -1, total = hash[i].length; ++j < total;)
                        {
                            str += '<input type="hidden" name="' + p + '" value="' + hash[i][j] + '">';
                        }
                    }
                }
                else
                {
                    str += this.embedData(hash[i], '', prefixTmp);
                }
            }

            ++iterator;
        }

        if(!_.isUndefined(hash) )
        {
            if(this.initialized)
            {
                this.parent.change();
            }
            if(!_.isUndefined(hash.filename) && !_.isUndefined(hash.category))
            {
                return this.embedDataFile(hash, str, prefix);
            }
            else
            {
                return str;
            }
        }
    },

    embedDataFile: function(hash, str, prefix)
    {
        var thumb = {};

        switch(hash.category)
        {
            case 'image': 
                            thumb.url = $.Tapioca.config.filesUrl + this.appslug + '/image/preview-'+hash.filename;
                            break;
            case 'video':
                            thumb.icon = 'film'
                            break;
            default:
                            thumb.icon = 'file'
        }

        thumb.str = str;

        return this.tplThumb({
            hash:  hash,
            thumb: thumb,
            prefix: prefix
        });
    }
})