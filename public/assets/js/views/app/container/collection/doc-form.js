
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
        this.factory   = new formFactory();

        this.tplEmbedRef = Handlebars.compile( $.Tapioca.Tpl.app.container.collection['embed-ref'] );

        this.factory.walk( this.schema, '', '');

        this.getDependencies();

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


        return this;
    },

    events:
    {
        // 'click .array-repeat-trigger'                                        : 'addNode',
        // 'click .input-repeat-list li:last-child .input-repeat-trigger'       : 'addInput',
        // 'click .input-repeat-list li:not(:last-child) .input-repeat-trigger' : 'removeInput',
        'click .doc-list-trigger'                                            : 'docList',
        'click .doc-remove-trigger'                                          : 'docRemove',
        // 'click .file-list-trigger'                                           : 'fileList',
        // 'click .file-remove-trigger'                                         : 'fileRemove',
        // 'document:addFile'                                                   : 'addFile',
        'document::addDoc'                                                    : 'addDoc'
    },


    change: function()
    {
        this.parent.change();
    },

    getDependencies: function()
    {
        var dependencies = this.factory.getDependencies(),
            total        = dependencies.length,
            loaded       = 0,
            library      = false,
            self         = this,
            callback     = function()
            {
                ++loaded;

                if( loaded == total )
                    self.render();
            };

        if( total )
        {
            for( var i = -1; ++i < total; )
            {
                var d = dependencies[ i ];

                if( d.type == 'collection' )
                {
                    var collection = $.Tapioca.UserApps[ this.appslug ].data[ d.namespace ].abstracts;

                    if( !collection.isFetched() )
                        collection.fetch({
                            success: callback
                        });
                    else
                        callback();
                }
                else
                {
                    if( !library )
                    {
                        var library = $.Tapioca.UserApps[ this.appslug ].library;

                        if( !library.isFetched() )
                        {
                            library.fetch({
                                success: callback
                            });
                        }
                    }
                    else
                        callback();
                } // d.type
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

    docPreview: function(data, digest, prefix)
    {
        return this.tplEmbedRef({
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
        var $target = $(event.target);
        var $parent = $target.parents('table').eq(0);

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

    bindInput: function()
    {
        var self    = this,
            _parent = this.parent;

        this.$el.find('table[data-dbref=true]').each(function()
        {
            var $parent = $(this).prev('div.btn-group').eq(0).hide();
        });

        this.$el.find('textarea').not('[data-binded="true"]').each(function()
        {
            var $this    = $(this),
                config   = ($this.attr('data-wysiwyg')) ? {} : { air: true},
                settings = $.extend({}, 
                {
                    buttons:       ['html', '|', 'bold', 'italic', 'link'],
                    airButtons:    ['bold', 'italic', 'link'],
                    keyupCallback: _.bind( _parent.change, _parent),
                    paragraphy:    false
                }, config);

            $this
                .redactor(settings)
                .attr('data-binded', 'true');
        });

        this.$el.find('input[type="date"]').not('[data-binded="true"]').each(function()
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

    embedData: function(hash, str, prefix)
    {

        var iterator = 0;

        for(var i in hash)
        {
            var prefixTmp = prefix + '.' + i;

            if(_.isString(hash[i]) || _.isNumber(hash[i]))
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

        if(!_.isUndefined(hash))
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

})