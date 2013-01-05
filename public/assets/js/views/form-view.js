
/*
 * validation inspired by validate.js by Rick Harrison, http://rickharrison.me
 * validate.js is open sourced under the MIT license.
 * Portions of validate.js are inspired by CodeIgniter.
 */

$.Tapioca.Views.FormView = $.Tapioca.Views.Content.extend({

    inlineValidation: true, 
    $btnSubmit:       false, 
    fields:           [],
    handlers:         {},
    arrowKeyCode:     [37, 38, 39, 40],

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
    events: {
        'keyup :input:not([data-bypass])'                                    : 'change',
        'change :input:not([data-bypass])'                                   : 'change',
        'keypress :input:not(textarea)'                                      : 'onEnter',
        'click button[type="submit"]'                                        : 'submit',
        'click button[type="reset"]'                                         : 'cancel',
        'click .input-repeat-list li:last-child .input-repeat-trigger'       : 'addRepeatNode',
        'click .input-repeat-list li:not(:last-child) .input-repeat-trigger' : 'removeRepeatNode'
    },

    change: function( event )
    {
        if( !_.isUndefined( event ) )
        {
            // Do not trigger `change`on cursor move
            if( $.inArray( event.keyCode, this.arrowKeyCode ) !== -1)
                return;            
        }

        if( !_.isUndefined( this.model.validate ) )
        {
            var $target = $(event.target),
                name    = $target.attr('name'),
                value   = $target.val();

            // reset
            $target.removeClass('alert');

            var errorMessage = this.model.validate(name, value);

            if(!_.isBlank(errorMessage))
            {
                $target.addClass('alert');
            }
        }

        this.unLoadToken = $.Tapioca.BeforeUnload.set(true, this.unLoadToken);

        if( !this.$btnSubmit )
        {
            this.$btnSubmit = this.$el.find('button[type="submit"]');

            this.$btnSubmit.removeClass('disabled').removeAttr('disabled');
        }
        
    },

    onEnter: function(event)
    {
        if (event.keyCode != 13) return;

        // prevent bubbling
        // event.stopPropagation();
        // event.preventDefault();

        this.submit(event);
    },


    cancel: function()
    {
        $.Tapioca.BeforeUnload.clean();

        window.history.back();
    },

    // view override
    addRepeatNode: function() {},

    removeRepeatNode: function( event )
    {
        $( event.target ).parents('li').remove();
    },

    resetForm: function()
    {
        $.Tapioca.BeforeUnload.clean();

        this.$btnSubmit.button('reset');
        this.$btnSubmit.attr('disabled', 'disabled').addClass('disabled');
    },

    displayErrors: function()
    {
        for(var i = -1, l = this.errors.length; ++i < l;)
        {
            var that   = this.errors[i],
                $input = $(that.element);
            
            $input.after('<p class="help-block">' + that.message + '</p>');
            $input.parents('div.control-group').addClass('error');
        }
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
             
            if (failed)
            {
                var args = null;

                if (param)
                    args = (this.fields[param]) ? this.fields[param].display : param;
                
                var message = $.Tapioca.I18n.get('rules.' + method, field.display, args);

                // Mike's Hack
                var _obj = {
                    message: message,
                    field:   field,
                    element: element
                }
                
                this.errors.push(_obj);

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
        this.$el.find('div.control-group').removeClass('error');
        this.$el.find('p.help-block').remove();
    
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
            this.displayErrors();
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
            return this.emailRegex.test(field.value);
        },
        
        min_length: function(field, length) {
            if (!this.numericRegex.test(length)) {
                return false;
            }
            
            return (field.value.length >= length);
        },
        
        max_length: function(field, length) {
            if (!this.numericRegex.test(length)) {
                return false;
            }
            
            return (field.value.length <= length);
        },
        
        exact_length: function(field, length) {
            if (!this.numericRegex.test(length)) {
                return false;
            }
            
            return (field.value.length == length);
        },
        
        greater_than: function(field, param) {
            if (!this.decimalRegex.test(field.value)) {
                return false;
            }

            return (parseFloat(field.value) > parseFloat(param));
        },
        
        less_than: function(field, param) {
            if (!this.decimalRegex.test(field.value)) {
                return false;
            }
            
            return (parseFloat(field.value) < parseFloat(param));
        },
        
        alpha: function(field) {
            return (this.alphaRegex.test(field.value));
        },
        
        alpha_numeric: function(field) {
            return (this.alphaNumericRegex.test(field.value));
        },
        
        alpha_dash: function(field) {
            return (this.alphaDashRegex.test(field.value));
        },
        
        numeric: function(field) {
            return (this.decimalRegex.test(field.value));
        },
        
        integer: function(field) {
            return (this.integerRegex.test(field.value));
        }
    }

});