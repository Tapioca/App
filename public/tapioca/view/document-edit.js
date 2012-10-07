define([
	'tapioca',
	'Handlebars',
	'aura/mediator',
	'view/content',
	'text!template/content/document-edit.html',
	'hbs!template/content/document-revision',
	'template/helpers/isSelected',
	'template/helpers/atLeastOnce',
	'template/helpers/localeSwitcher',
	'underscore.string',
	'form2js',
	'dropdown',
	'template/helpers/setStatus',
	'hbs!template/content/document-thumb',
	'hbs!template/content/document-ref',
	'jqueryui',
	'redactor'
], function(tapioca, Handlebars, mediator, vContent, tContent, tRevisions, isSelected, atLeastOnce, localeSwitcher, _s, form2js, dropdown, setStatus, tThumb, tRef, wysiwyg)
{

	/*
	 * validation based on validate.js 1.0.1 by Rick Harrison, http://rickharrison.me
	 */

	var view = vContent.extend(
	{
		template: tContent,    // Handlebars template
		formStr: null,         // html string, partial Handlebars template
		counters: {},          // object that keep the count ok increment for loops
		initialized: false,    // prevent this.change() on page init
		errors: [],
		fields: {},
		messages: {},
		handlers: {},

		messages: {
		    required: 'The %s field is required.',
		    matches: 'The %s field does not match the %s field.',
		    valid_email: 'The %s field must contain a valid email address.',
		    min_length: 'The %s field must be at least %s characters in length.',
		    max_length: 'The %s field must not exceed %s characters in length.',
		    exact_length: 'The %s field must be exactly %s characters in length.',
		    greater_than: 'The %s field must contain a number greater than %s.',
		    less_than: 'The %s field must contain a number less than %s.',
		    alpha: 'The %s field must only contain alphabetical characters.',
		    alpha_numeric: 'The %s field must only contain alpha-numeric characters.',
		    alpha_dash: 'The %s field must only contain alpha-numeric characters, underscores, and dashes.',
		    numeric: 'The %s field must contain only numbers.',
		    integer: 'The %s field must contain an integer.'
		},

		initialize: function(options)
		{
			var self     = this;

			this.schema     = options.schema.toJSON();
			this.structure  = this.schema.structure;
			this.appSlug    = options.appSlug;
			this.namespace  = options.namespace;
			this.ref        = options.ref;

			this.locale     = tapioca.apps[this.appSlug].locale;
			this.rootUri    = tapioca.config.base_uri+this.appSlug+'/document/'+this.namespace;
			this.baseUri    = (!_.isNull(this.ref)) ? this.rootUri+'/'+options.ref : this.rootUri+'/new';

			_.bindAll(this, 'preRender');
			this.model.on('change', this.preRender);
			//this.model.bind('reset', this.render, this);

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
				var prefix = options.hash.prefix;
					prefix =  prefix.replace(/_#/g, '[{{').replace(/#_/g, '}}]').replace(/II/g, '"');
				var template = Handlebars.compile(prefix);
				prefix = template({})

				return self.embedData(context, '', prefix);
			});
			Handlebars.registerHelper('_embedDoc', function(context, options)
			{
				if(!_.isUndefined(context))
				{
					// NESTED HELPERS NOT ALLOWED 
					// https://github.com/wycats/handlebars.js/issues/222
					var prefix = options.hash.prefix;
						prefix =  prefix.replace(/_#/g, '[{{').replace(/#_/g, '}}]').replace(/II/g, '"');
					var template = Handlebars.compile(prefix);
					prefix = template({})

					var url   = tapioca.config.api_uri+self.appSlug+'/document/'+options.hash.collection+'/'+context.ref+'?mode=summary',
						summary,
						hxr = $.ajax({
										url: url,
										dataType: 'json',
										async: false,
										success: function(data)
										{
											summary = data;
										}
									});

					return self.docPreview(context, summary, prefix);
				}
			});

			if(options.forceRender)
			{
				this.preRender();
			}
		},

		events:
		{
			'keyup :input'                                                       : 'change',
			'click #tapioca-document-form-save'                                  : 'save',
			'click .array-repeat-trigger'                                        : 'addNode',
			'click .input-repeat-list li:last-child .input-repeat-trigger'       : 'addInput',
			'click .input-repeat-list li:not(:last-child) .input-repeat-trigger' : 'removeInput',
			'click .doc-list-trigger'                                            : 'docList',
			'click .doc-remove-trigger'                                          : 'docRemove',
			'click .file-list-trigger'                                           : 'fileList',
			'click .file-remove-trigger'                                         : 'fileRemove',
			'document:addFile'                                                   : 'addFile',
			'document:addDoc'                                                    : 'addDoc',
			'keypress input'                                                     : 'onEnter'
		},

		onEnter: function(event)
		{
			if (event.keyCode != 13) return;

			// prevent bubbling
			event.stopPropagation();
			event.preventDefault();

			this.save();
		},

		change: function()
		{
			tapioca.beforeunload = {
				type: 'confirm',
				title: 'Etes vous sur de voiloir quiter cette page ? ',
				message: 'Vos modifications ne seront pas sauvegarder'
			};

			$('#tapioca-document-form-save').removeClass('disabled').removeAttr('disabled');
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

		        if (parts = ruleRegex.exec(method)) {
		            method = parts[1];
		            param = parts[2];
		        }
		        
		        /*
		         * If the hook is defined, run it to find any validation errors
		         */
		        
		        if (typeof _hooks[method] === 'function') {
		            if (!_hooks[method].apply(this, [field, param])) {
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
			for (var i = 0, fieldLength = fields.length; i < fieldLength; i++) 
			{
				var field = fields[i];

				this.fields[field.name] = {
						name: field.name,
						display: field.display || field.name,
						rules: field.rules,
						type: null,
						value: null,
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
	                var field = this.fields[key] || {},
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

		save: function(event)
		{
			tapioca.beforeunload = false;

			this.$el.find('div.control-group').removeClass('error');
			this.$el.find('p.help-block').remove();

			if( this.validateForm() )
			{
				var formData = form2js('tapioca-document-form', '.'),
					self     = this,
					isNew    = this.model.isNew();

				this.model.save(formData, {
					success:function (model, response)
					{
						// prevent this.change()  to be trigged on render
						this.initialized = false;

						if(isNew)
						{
							var ref   = self.model.get('_ref');
								route = tapioca.app.router.reverse('documentRef'),
								href  = tapioca.app.router.createUri(route, [self.appSlug, self.namespace, ref]);

							Backbone.history.navigate(href, true);
						}
					}
				});
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

		walk: function(_structure, _prefix, _previous_key)
		{
			_.each(_structure, function(item, key)
			{
				var prefix_tmp = '';
				var _key = (!_s.isBlank(_previous_key)) ? _previous_key+'.'+key : key;

				// Recurssion
				if(!_.isUndefined(item.node))
				{
					// start new fieldset
					this.formStr.define('open', item, _prefix, _key);

					var isArray = (item.type == 'array');
					prefix_tmp   = this.formStr.prefix(prefix_tmp, _prefix, item.id, isArray);

					if(isArray)
					{
						this.formStr.define('incCounter', item);
					}

					if(_.isUndefined(item.template))
					{
						this.walk(item.node, prefix_tmp, _key);
					}
					else
					{
						this.formStr.define('template', item.template, null, null);
					}

					this.formStr.define('close', item, _prefix, _key);
				}
				else
				{
					this.formStr.define('row', item, _prefix, _key);
					//this.formStr.setItem(item, _prefix, _key)
				}

			}, this);
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

			prefix = this.formStr.prefix('', prefixTmp, node.id, isArray);

			return {
				$: $target,
				node: node,
				prefix: prefix,
				key: key
			}
		},

		addInput: function(event)
		{
			var target      = this.targetData(event),
				type        = this.formStr.getType(target.node.type),
				prefixCheck = target.prefix.split('.');
				lastIndex   = (prefixCheck.length - 1);

			prefixCheck.splice(lastIndex, 1);
			
			target.prefix       = prefixCheck.join('.');
			target.node.pattern = true;

			this.formStr.define(type, target.node, target.prefix, target.key);

			var htmlStr  = this.formStr.get(),
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

			this.formStr.define('loopStart', target.node);
			this.formStr.define('incCounter', target.node);

			if(_.isUndefined(target.node.template))
			{
				this.walk(target.node.node, target.prefix, target.key);
			}
			else
			{
				this.formStr.define('template', target.node.template, null, null);
			}

			this.formStr.define('loopEnd', null);

			var htmlStr  = this.formStr.get(),
				template = Handlebars.compile(htmlStr),
				html     = template({});
			
			target.$.parents('p.align-right').before(html);

			this.bindInput();
		},

		docList: function(event)
		{
			this.target    = event;
			var target     = this.targetData(this.target);

			this.docCollection = target.$.attr('data-collection');
			this.docEmbedded   = target.$.attr('data-embedded');
			
			mediator.publish('callCollectionRef', this.appSlug, this.docCollection, this.locale.working.key);
		},

		addDoc: function(event, doc)
		{
			var target   = this.targetData(this.target),
				_html    = '',
				ret      = {ref: doc._ref};

			if(!_.isUndefined(this.docEmbedded))
			{
				var url   = tapioca.config.api_uri+this.appSlug+'/document/'+this.docCollection+'/'+doc._ref+'?locale='+this.locale.working.key,
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

			_html = this.docPreview(ret, doc, target.prefix);

			var $parent = target.$.parents('div.btn-group').eq(0);

			$parent.after(_html);
			$parent.hide();
		},

		docPreview: function(data, summary, prefix)
		{
			return tRef({
				doc: summary,
				fields: {
					str: this.embedData(data, '', prefix)
				}
			});
		},

		docRemove: function(event)
		{
			var $target = $(event.target);
			var $parent = $target.parents('table').eq(0);

			$parent.prev('div.btn-group').eq(0).show();
			$parent.remove();
		},

		fileList: function(event)
		{
			this.target = event;
			mediator.publish('callFileList', this.appSlug);
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
		},

		fileRemove: function(event)
		{
			$(event.target).parents('ul.thumbnails').remove();
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
					this.change();
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
								thumb.url = tapioca.config.file.base_path + '/' + this.appSlug + '/image/preview-'+hash.filename;
								break;
				case 'video':
								thumb.icon = 'film'
								break;
				default:
								thumb.icon = 'file'
			}

			thumb.str = str;

			return tThumb({
				hash: hash,
				thumb: thumb
			});
		},

		getDescendantProp: function(key)
		{
			var keys = key.split('.'),
				ret  = this.structure,
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

			// clean new object from global paterns
//			if(ret.repeat)
//			{
//				delete ret.repeat;
//			}

 			return ret;
		},

		preRender: function()
		{
			if(!_.isNull(this.ref))
			{
				var model     = this.model.toJSON();
				var revisions = model._about.revisions			
			}
			else
			{
				var revisions = {};
			}

			this.html(tContent, 'app-form');
			this.revisionsRender(revisions);
			this.formRender();
		},

		revisionsRender: function(revisions)
		{
			var html  = tRevisions({
								baseUri: this.baseUri,
								revisions: revisions,
								ref: this.ref,
								appslug: this.appSlug,
								namespace: this.namespace
							});
			
			$('#revisions').html(html);

			var self = this;
			
			this.$el.find('ul[data-type="set-status"] a').setStatus(function(data)
			{
				self.revisionsRender(data.revisions);
			});
		},

		formRender: function(eventName)
		{
			this.formStr = new fieldsFactory();

			this.walk(this.structure, '', '');

			var formStr  = this.formStr.get();
				formStr += '{{/model}}';
//console.log(formStr)
			var template = Handlebars.compile(formStr);
			var docTitle = (!_.isNull(this.model.get('_ref'))) ? 'Edit document' : 'Compose new document';
			var html     = template({
								docTitle: docTitle,
								model: this.model.toJSON(),
								baseUri: this.baseUri,
								locale: this.locale
							});

			$('#form-holder').html(html);

			this.$el.find('.dropdown-toggle').dropdown();

			this.bindInput();

			this.initialized = true;

			this.form = document.forms['tapioca-document-form'];

			console.log(this.fields);

			return this;
		},

		bindInput: function()
		{

			var self = this;

			this.$el.find('table[data-dbref=true]').each(function()
			{
				var $parent = $(this).prev('div.btn-group').eq(0).hide();
			});

			this.$el.find('textarea').not('[data-binded="true"]').each(function()
			{
				var $this  = $(this),
					config = ($this.attr('data-wysiwyg')) ? {} : { air: true},
					settings = $.extend({}, {
						buttons: ['html', '|', 'bold', 'italic', 'link'],
						airButtons: ['bold', 'italic', 'link'],
						keyupCallback: self.change,
						paragraphy: false
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
						}) 
						$altField.val(epoch / 1000);
						self.change();
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

				self.addRules( [_rule] );	

				$this.attr('data-ruled', 'true');
			})

//			this.$el.find('input[name="title"]').keyup(this.slugiffy);
		},

		slugiffy: function(event)
		{
console.log(event)
		},

		onClose: function()
		{
			tapioca.beforeunload = false;
			//_.bindAll(this, 'render');
			this.model.unbind('change', this.render);
			//this.model.unbind('reset', this.render);
		}
	});


	var fieldsFactory = function()
	{
		var formHtml =  '<fieldset>\
							<legend>{{docTitle}}</legend>\
							<div class="dropdown btn-group" id="locale-switch">\
								<a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">\
									{{locale.working.label}}\
									<b class="caret"></b>\
								</a>\
								<ul class="dropdown-menu pull-right">\
								{{{localeSwitcher locale.list baseUri}}}\
								</ul>\
							</div>{{#model}}',
			firstFieldset =  '</fieldset>',
			firstFieldsetClose = false,
			wysiwygInc = 0,
			inc = 0;

		// Field name Helpers

		var setPrefix = function(_prefix, _isArray, _id)
		{
			if(_isArray && _prefix != '')
			{
				_prefix = _prefix + '[{{_getCounter counter="'+ _id +'"}}]';
			}
			
			else if(_prefix != '')
			{
				_prefix = _prefix;
			}
			
			return _prefix;
		};

		var setRules = function(_item, _prefix)
		{
			// Rules
			if(!_.isUndefined(_item.rules))
			{
				var _rules_str = _item.rules.join('|');

				return ' data-rules="' + _rules_str + '" data-label="' + _item.label + '"';
			}

			return '';
		};

		var getName = function(_item, _prefix)
		{
			var _name = '';

			_prefix = setPrefix(_prefix, false, _item.id);
			
			if(_prefix != '')
			{
				_prefix = _prefix + '.';
			}
			
			_name = _prefix + _item.id;

			if(!_.isUndefined(_item.repeat) || _item.type == 'checkbox')
			{
				_name += '[]';
			}
			
			return _name;
		}

		var getType = function(type)
		{
			// Find the right item's type
			// will be usefull for template

			switch(type)
			{
				case 'textarea':
				case 'select':
				case 'file':
				case 'bool':
				case 'dbref':
								break;
				case 'radio':
				case 'checkbox':
								type = 'group';
								break;

				default:
								type = 'input';
			}

			return type;
		};

		// Field definition

		var fields = {};


		fields.incCounter = function(item)
		{
			formHtml += '{{_incCounter counter="' + item.id + '"}}'
		}

		fields.loopStart = function(item)
		{
			formHtml += '{{#atLeastOnce ' + item.id + ' type="' + item.type + '"}}'
		}
		
		fields.loopEnd = function()
		{
			formHtml += '<hr>{{/atLeastOnce}}';
		}

		fields.open = function(item)
		{
			if(!firstFieldsetClose)
			{
				firstFieldsetClose = true;
				formHtml += firstFieldset;
			}

			var str = '<fieldset class="subgroup">';

			if(!_.isUndefined(item.label) && !_s.isBlank(item.label))
			{
				str += '<legend>'+ item.label +'</legend>';
			}
			
			formHtml += str;

			this.loopStart(item);
			
			
		};

		fields.close = function(item, prefix, key)
		{
			fields.loopEnd();

			if(item.type == 'array')
			{
				formHtml += '<p class="align-right">\
					<a class="btn btn-mini array-repeat-trigger" data-prefix="' + getName(item, prefix) + '" data-key="'+key+'" href="javascript:void(0);">\
						<i class="icon-plus"></i>\
						Ajouter\
					</a>\
				</p>';
			}

			formHtml += '</fieldset>';

		};

		fields.input = function(item, prefix, key)
		{
			var str = '',
				id  = (item.repeat && !item.pattern) ? 'this' : item.id;

			if(item.repeat && !item.pattern)
			{
				str += '<ul class="input-repeat-list">{{#atLeastOnce ' + item.id + ' type="array"}}<li>'
			}

			if(item.repeat && item.pattern)
			{
				str += '<li>';
			}

			str += '<input type="'+item.type+'" ' + setRules(item, prefix) + ' class="';
			str += (_.isUndefined(item.class)) ? 'span7' : item.class; 
			str += '"';
			str += (item.type =='date') ? ' data-' : ' ';
			str += 'name="' + getName(item, prefix) + '"';
			str += (item.type =='date') ? ' readonly="readonly"' : ' ';
			str += (item.type =='date') ? ' value="{{displayDate ' + id + ' format="DD/MM/YYYY"}}"' : '  value="{{' + id + '}}"';
			str += '>';

			if(item.type =='date')
			{
				str += '<input type="hidden" name="' + getName(item, prefix) + '" value="{{' + id + '}}">';
			}

			if(item.repeat)
			{
				str += '<a href="javascript:void(0)" class="btn btn-mini input-repeat-trigger" data-prefix="' + getName(item, prefix) + '" data-key="'+key+'"><i class="icon-repeat-trigger"></i></a>';
			}

			if(item.repeat && item.pattern)
			{
				str += '</li>';
			}

			if(item.repeat && !item.pattern)
			{
				str += '{{/atLeastOnce}}</li></ul>';
			}

			formHtml += str;
		};

		fields.textarea = function(item, prefix)
		{
// 			if(!_.isUndefined(item.wysiwyg))
// 			{
// 				++wysiwygInc;
// 				formHtml += '<div id="wysihtml5-toolbar-'+wysiwygInc+'" class="wysihtml5-toolbar" style="display: none;">'+"\n"+
// '  <a data-wysihtml5-command="bold" title="bold"><i class="icon-bold"></i></a>'+"\n"+
// '  <a data-wysihtml5-command="italic" title="italic"><i class="icon-italic"></i></a>'+"\n"+
// '  <span class="separator">&nbsp;</span>'+"\n"+
// '  <a data-wysihtml5-command="createLink" title="insert link"><i class="icon-link"></i></a>'+"\n"+
// '  <span class="separator">&nbsp;</span>'+"\n"+
// '  <a data-wysihtml5-command="insertOrderedList" title="insert ordered list"><i class="icon-list-ol"></i></a>'+"\n"+
// '  <a data-wysihtml5-command="insertUnorderedList" title="insert unordered list"><i class="icon-list-ul"></i></a>'+"\n"+
// '  <span class="separator">&nbsp;</span>'+"\n"+
// '  <a data-wysihtml5-command="change_view" title="Show HTML"><i class="icon-list-ul"></i></a>'+"\n"+
// '  <div data-wysihtml5-dialog="createLink" style="display: none;">'+"\n"+
// '    <label>'+"\n"+
// '      Link:'+"\n"+
// '      <input data-wysihtml5-dialog-field="href" value="http://" class="text"> '+"\n"+
// '      <a data-wysihtml5-dialog-action="save" title="save"><i class="icon-ok"></i></a> <a data-wysihtml5-dialog-action="cancel" title="cancel"><i class="icon-remove"></i></a>'+"\n"+
// '    </label>'+"\n"+
// '  </div>'+"\n"+
// '</div>';
// 			}

			formHtml += '<textarea class="span7"';
			
			if(!_.isUndefined(item.wysiwyg))
			{
				formHtml += ' data-wysiwyg="true"'
				// data-toolbar="wysihtml5-toolbar-'+wysiwygInc+'" id="wysihtml5-textarea-'+wysiwygInc+'"
			}

			formHtml += ' name="' + getName(item, prefix) + '" rows="3">{{' + item.id + '}}</textarea>';
		};

		fields.select = function(item, prefix)
		{
			var klass = (_.isUndefined(item.className)) ? '': item.className;

			var str = '<select name="' + getName(item, prefix) + '" class="'+klass+'">';

			if(!_.isUndefined(item.source))
			{
				item.options = null; //Tapp.Documents.Form.GetSource(_element.source);
			}
			
			var options = '';
			
			for (var i in item.options)
			{
				// option group
				if(_.isArray(item.options[i]))
				{
					options += '<optgroup label="' + i + '">'+"\n";
					
					for(var j = -1, nbOptions = item.options[i].length; ++j < nbOptions;)
					{
						options += '<option value="' + item.options[i][j].value +'">' + item.options[i][j].label + "</option>\n";
					}

					options += '</optgroup>'+"\n";
				}
				else
				{
					options += '<option value="' + item.options[i].value +'"{{isSelected '+ item.id + ' default="' + item.options[i].value +'" attribute="selected"}}>' + item.options[i].label + "</option>\n";

				}
			}

			str += options;
			str += '</select>';

			formHtml += str;
		};

		fields.file = function(item, prefix, key)
		{
			// NESTED HELPERS NOT ALLOWED 
			// https://github.com/wycats/handlebars.js/issues/222
			var _prefix =  getName(item, prefix).replace(/\[{{/g, '_#').replace(/}}\]/g, '#_').replace(/"/g, 'II')

			formHtml += '{{{ _embedData ' + item.id + ' prefix="' + _prefix + '" }}}\
						<div class="btn-group float-left">\
							<a class="btn file-list-trigger" href="javascript:void(0)" data-prefix="' + getName(item, prefix) + '" data-key="'+key+'">\
								<i class="icon-file"></i>\
								library\
							</a>';

			if(!_.isUndefined(item.upload))
			{
				formHtml += '<a class="btn" href="javascript:void(0)">\
								<i class="icon-upload"></i>\
								upload\
							</a>';
			}

			formHtml += '</div>';
		};

		fields.dbref = function(item, prefix, key)
		{
			// NESTED HELPERS NOT ALLOWED 
			// https://github.com/wycats/handlebars.js/issues/222
			var _prefix =  getName(item, prefix).replace(/\[{{/g, '_#').replace(/}}\]/g, '#_').replace(/"/g, 'II')

			formHtml += '<div class="btn-group float-left">\
							<a class="btn doc-list-trigger" href="javascript:void(0)" data-prefix="' + getName(item, prefix) + '" data-key="'+key+'" data-collection="' + item.collection + '"';

			if(!_.isUndefined(item.embedded))
			{
				formHtml += ' data-embedded="' + item.embedded.join('::') + '"';
			}

			formHtml += '>\
								<i class="icon-file"></i>\
								Select\
							</a>\
						</div>\
						{{{ _embedDoc ' + item.id + ' prefix="' + _prefix + '" collection="' + item.collection + '"}}}';
		};

		fields.row = function(item, prefix, key)
		{

			var type = getType(item.type)

			formHtml += '<div class="control-group">';
			
			if(item.label != '')
			{
				formHtml += '<label class="control-label">'+item.label+'</label>';
			}

			formHtml += '<div class="controls">';
			fields[type](item, prefix, key);
			formHtml += '</div></div>';
		};

		fields.bool = function(item, prefix, key)
		{
			formHtml += '<input type="checkbox" value="1" name="' + getName(item, prefix) + '"{{isSelected '+ item.id + ' default="1" attribute="checked"}}>';
		};

		fields.template = function(str)
		{
			formHtml += str;
		};

		// Getter//Setter

		return {
			'getName': getName,
			'getType': getType,
			'define': function(type, item, prefix, key)
			{
				fields[type](item, prefix, key);
			},
			'prefix': function(_prefix_tmp, _prefix, _id, _is_array)
			{
				_prefix_tmp = (_prefix != '') ? _prefix + '.' + _id : _id;
				return setPrefix(_prefix_tmp, _is_array, _id);
			},
			'get': function()
			{
				if(!firstFieldsetClose)
				{
					firstFieldsetClose = true;
					formHtml += firstFieldset;
				}

				var result = formHtml;
				formHtml = '';

				return result;
			}
		}
	}

    /*
     * Define the regular expressions that will be used
     */
    
    var ruleRegex = /^(.+)\[(.+)\]$/,
        numericRegex = /^[0-9]+$/,
        integerRegex = /^\-?[0-9]+$/,
        decimalRegex = /^\-?[0-9]*\.?[0-9]+$/,
        emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,6}$/i,
        alphaRegex = /^[a-z]+$/i,
        alphaNumericRegex = /^[a-z0-9]+$/i,
        alphaDashRegex = /^[a-z0-9_-]+$/i;

    /*
     * @private
     * Object containing all of the validation hooks
     */
    
    var _hooks = {
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
    };


	return view;
});