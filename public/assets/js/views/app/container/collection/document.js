
$.Tapioca.Views.Document = $.Tapioca.Views.FormView.extend(
{
    loaded:   0,
    total:    4,
    fields:   [],
    handlers: {},

    messages: {
        required:      'The %s field is required.',
        matches:       'The %s field does not match the %s field.',
        valid_email:   'The %s field must contain a valid email address.',
        min_length:    'The %s field must be at least %s characters in length.',
        max_length:    'The %s field must not exceed %s characters in length.',
        exact_length:  'The %s field must be exactly %s characters in length.',
        greater_than:  'The %s field must contain a number greater than %s.',
        less_than:     'The %s field must contain a number less than %s.',
        alpha:         'The %s field must only contain alphabetical characters.',
        alpha_numeric: 'The %s field must only contain alpha-numeric characters.',
        alpha_dash:    'The %s field must only contain alpha-numeric characters, underscores, and dashes.',
        numeric:       'The %s field must contain only numbers.',
        integer:       'The %s field must contain an integer.'
    },

    /*
     * Define the regular expressions that will be used
     */
    
    ruleRegex:         /^(.+)\[(.+)\]$/,
    numericRegex:      /^[0-9]+$/,
    integerRegex:      /^\-?[0-9]+$/,
    decimalRegex:      /^\-?[0-9]*\.?[0-9]+$/,
    emailRegex:        /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,6}$/i,
    alphaRegex:        /^[a-z]+$/i,
    alphaNumericRegex: /^[a-z0-9]+$/i,
    alphaDashRegex:    /^[a-z0-9_-]+$/i,

    initialize: function( options )
    {
        this.$el.appendTo('#app-content');

        this.appslug   = options.appslug;
        this.namespace = options.namespace;
        this.ref       = options.ref;
        this.revision  = options.revision;
        this.locale    = options.locale,

        this.isNew     = options.isNew;
        this.baseUri   = options.baseUri;
        
        this.schema    = options.schema;
        this.abstracts = options.abstracts;
        // this.doc       = options.doc;
        this.users     = options.users;

        // is that bad???
        // this model is required form change()
        // but this doc is more clean as name
        this.model = this.doc = options.doc;

        if( !this.isNew )
            this.doc.fetch({ 
                data: options.docOptions,
                success: _.bind( this.ressourcesLoaded, this )
            });
        else
            ++this.loaded;

        if( !this.users.isFetched() )
            this.users.reload( _.bind( this.ressourcesLoaded, this ) );
        else
            ++this.loaded;

        if( this.schema.hasSchema() )
            this.schema.fetch({
                success: _.bind( this.ressourcesLoaded, this )
            });
        else
            ++this.loaded;

        if( !this.abstracts.isFetched() )
            this.abstracts.fetch({
                success: _.bind( this.ressourcesLoaded, this )
            });
        else
            ++this.loaded;

        return this;
    },

    ressourcesLoaded: function()
    {

        ++this.loaded;

        if( this.loaded == this.total )
        {
            this.renderRev();
            this.renderDoc();
        }

        return this;
    },

    getPageTitle: function()
    {
        return ( this.isNew ) ? __('title.new_document') : __('title.edit_document');
    },

    render: function()
    {
        var pageTitle = this.getPageTitle();

        var tpl  = Handlebars.compile( $.Tapioca.Tpl.app.container.collection.document ),
            html = tpl({
                appslug:    this.appslug,
                locale:     this.locale,
                baseUri:    this.baseUri,
                isNew:      this.isNew,
                pageTitle:  pageTitle
            });

        this.html( html, 'app-form');

        this.form = document.getElementById('tapioca-document-form');

        return this;
    },

    renderRev: function()
    {
        var abstract = ( this.isNew ) ? new $.Tapioca.Models.Abstract() : this.abstracts.get( this.ref );

        this.vRevisions = new $.Tapioca.Views.Revisions({
            model:    abstract,
            isNew:    this.isNew,
            appslug:  this.appslug,
            baseUri:  this.baseUri,
            locale:   this.locale,
            revision: this.revision,
            users:    this.users
        }).render();

    },

    renderDoc: function()
    {
        this.vDocument = new $.Tapioca.Views.DocForm({
            model:   this.doc,
            schema:  this.schema,
            appslug: this.appslug,
            baseUri: this.baseUri,
            locale:  this.locale,
            parent:  this
        });
    },

    submit: function()
    {
        this.$el.find('div.control-group').removeClass('error');
        this.$el.find('p.help-block').remove();

        if( this.validateForm() )
        {
        //     var formData = form2js('tapioca-document-form', '.'),
        //         self     = this,
        //         isNew    = this.model.isNew();

        //     this.model.save(formData, {
        //         success:function (model, response)
        //         {
        //             // prevent this.change()  to be trigged on render
        //             this.initialized = false;

        //             if(isNew)
        //             {
        //                 var ref   = self.model.get('_ref');
        //                     route = tapioca.app.router.reverse('documentRef'),
        //                     href  = tapioca.app.router.createUri(route, [self.appSlug, self.namespace, ref]);

        //                 Backbone.history.navigate(href, true);
        //             }
        //         }
        //     });
        }
        else
        {
            for(var i = -1, l = this.errors.length; ++i < l;)
            {
                var that   = this.errors[i],
                    $input = $(that.element);
                
                $input.after('<p class="help-block">' + that.message + '</p>');
                $input.parents('div.control-group').addClass('error');
            }
        }

        return false;
    },

    /*
     * Looks at the fields value and evaluates it against the given rules
     */

    validateField: function(field, element) {
        var rules = field.rules.split('|');
        
        /*
         * If the value is null and not required, we don't need to run through validation
         */
         
        if (field.rules.indexOf('required') === -1 && (!field.value || field.value === '' || field.value === undefined)) {
            return;
        }
        
        /*
         * Run through the rules and execute the validation methods as needed
         */
        
        for (var i = 0, ruleLength = rules.length; i < ruleLength; i++) {
            var method = rules[i],
                param = null,
                failed = false;

            /*
             * If the rule has a parameter (i.e. matches[param]) split it out
             */

            if (parts = this.ruleRegex.exec(method)) {
                method = parts[1];
                param = parts[2];
            }
            
            /*
             * If the hook is defined, run it to find any validation errors
             */
            
            if (typeof this._hooks[method] === 'function') {
                if (!this._hooks[method].apply(this, [field, param])) {
                    failed = true;
                }
            } else if (method.substring(0, 9) === 'callback_') {
                // Custom method. Execute the handler if it was registered
                method = method.substring(9, method.length);
                
                if (typeof this.handlers[method] === 'function') {
                    if (this.handlers[method].apply(this, [field.value]) === false) {
                        failed = true;
                    }
                }
            }
            
            /*
             * If the hook failed, add a message to the errors array
             */
             
            if (failed) {
                // Make sure we have a message for this rule
                var source = this.messages[method] || defaults.messages[method];
                
                if (source) {
                    
                    var message = source.replace('%s', field.display);
                    
                    if (param) {
                        message = message.replace('%s', (this.fields[param]) ? this.fields[param].display : param);
                    }
                    
                    // Mike's Hack
                    var _obj = {
                        message: message,
                        field: field,
                        element: element
                    }
                    
                    this.errors.push(_obj);
                } else {
                    this.errors.push('An error has occurred with the ' + field.display + ' field.');
                }
                
                // Break out so as to not spam with validation errors (i.e. required and valid_email)
                break;
            }
        }
    },

    addRules: function(fields)
    {
        for (var i = -1, l = fields.length; ++i < l;) 
        {
            var field = fields[i];

            this.fields[field.name] = {
                    name:    field.name,
                    display: field.display || field.name,
                    rules:   field.rules,
                    type:    null,
                    value:   null,
                    checked: null
                }
        }

        return this;
    },

    validateForm: function()
    {
        this.errors = [];
    
        for (var key in this.fields) {

            if (this.fields.hasOwnProperty(key))
            {
                var field   = this.fields[key] || {},
                    element = this.form[field.name];

                 // if array, run throught each element
                if((typeof(element) != "undefined") && (element.length > 1))
                {
                    for(var _i = 0; _i < element.length; ++_i)
                    {
                        this.setField(element[_i], field);
                    }

                }
                else
                {
                    this.setField(element, field);
                }
            }
        }

        if (this.errors.length > 0)
        {
            return false;
        }
        
        return true;
    },

    setField: function(element, field)
    {
        if (element && element !== undefined) {
            field.type = element.type;
            field.value = element.value;
            field.checked = element.checked;
        }
        
        /*
         * Run through the rules for each field.
         */
        this.validateField(field, element);
    },

    onClose: function()
    {
        if( this.vRevisions )
            this.vRevisions.close();

        if( this.vDocument )
            this.vDocument.close();
    },


    /*
     * @private
     * Object containing all of the validation hooks
     */
    
    _hooks: {
        required: function(field) {
            var value = field.value;
            
            if (field.type === 'checkbox') {
                return (field.checked === true);
            }
        
            return (value !== null && value !== '');
        },
        
        matches: function(field, matchName) {
            if (el = this.form[matchName]) {
                return field.value === el.value;
            }
            
            return false;
        },
        
        valid_email: function(field) {
            return emailRegex.test(field.value);
        },
        
        min_length: function(field, length) {
            if (!numericRegex.test(length)) {
                return false;
            }
            
            return (field.value.length >= length);
        },
        
        max_length: function(field, length) {
            if (!numericRegex.test(length)) {
                return false;
            }
            
            return (field.value.length <= length);
        },
        
        exact_length: function(field, length) {
            if (!numericRegex.test(length)) {
                return false;
            }
            
            return (field.value.length == length);
        },
        
        greater_than: function(field, param) {
            if (!decimalRegex.test(field.value)) {
                return false;
            }

            return (parseFloat(field.value) > parseFloat(param));
        },
        
        less_than: function(field, param) {
            if (!decimalRegex.test(field.value)) {
                return false;
            }
            
            return (parseFloat(field.value) < parseFloat(param));
        },
        
        alpha: function(field) {
            return (alphaRegex.test(field.value));
        },
        
        alpha_numeric: function(field) {
            return (alphaNumericRegex.test(field.value));
        },
        
        alpha_dash: function(field) {
            return (alphaDashRegex.test(field.value));
        },
        
        numeric: function(field) {
            return (decimalRegex.test(field.value));
        },
        
        integer: function(field) {
            return (integerRegex.test(field.value));
        }
    }
});