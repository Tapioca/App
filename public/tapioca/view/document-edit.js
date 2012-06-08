define([
	'tapioca',
	'Handlebars',
	'aura/mediator',
	'view/content',
	'text!template/content/document-edit.html',
	'template/helpers/isSelected',
	'template/helpers/atLeastOnce',
	'underscore.string',
	'form2js'
], function(tapioca, Handlebars, mediator, vContent, tContent, isSelected, atLeastOnce, _s, form2js)
{
	var view = vContent.extend(
	{
		template: tContent,    // Handlebars template
		formStr: null,         // html string, partial Handlebars template
		counters: {},          // object that keep the count ok increment for loops

		initialize: function(options)
		{
			this.schema     = options.schema.toJSON();
			this.structure  = this.schema.structure;
			this.appSlug    = options.appSlug;
			this.namespace  = options.namespace;

			_.bindAll(this, 'render');
			this.model.on('change', this.render);
			//this.model.bind('reset', this.render, this);

			var self     = this;

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
				return self.embedData(context, '', options.hash.prefix);
			});

			if(options.forceRender)
			{
				this.render();
			}
		},

		events:
		{
			'change :input'                                                      : 'change',
			'click #tapioca-document-form-save'                                  : 'save',
			'click .array-repeat-trigger'                                        : 'addNode',
			'click .input-repeat-list li:last-child .input-repeat-trigger'       : 'addInput',
			'click .input-repeat-list li:not(:last-child) .input-repeat-trigger' : 'removeInput',
			'click .file-list-trigger'                                           : 'fileList',
			'document:addFile'                                                   : 'addFile'
//			'click .delete': 'delete'
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

		save: function()
		{
			tapioca.beforeunload = false;

			var formData = form2js('tapioca-document-form', '.'),
				self     = this;
				
			this.model.save(formData, {
				success:function (model, response)
				{
					var route = tapioca.app.router.reverse('documentRef'),
						href  = tapioca.app.router.createUri(route, [self.appSlug, self.namespace, self.model.get('_ref')]);

					Backbone.history.navigate(href, true);
				}
			});

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

					this.walk(item.node, prefix_tmp, _key);

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
			this.walk(target.node.node, target.prefix, target.key);
			this.formStr.define('loopEnd', null);

			var htmlStr  = this.formStr.get(),
				template = Handlebars.compile(htmlStr),
				html     = template({});
			
			target.$.parents('p.align-right').before(html);
		},

		fileList: function(event)
		{
			this.target = event;
			mediator.publish('callFileList', this.appSlug);
		},

		addFile: function(event, file)
		{
			var target = this.targetData(this.target),
				_html  = '';
//console.log(target)
//console.log(file)
			_html = this.embedData(file, _html, target.prefix);
//console.log(_html);
			target.$.after(_html);
		},

		embedData: function(hash, str, prefix)
		{
			var iterator = 0;

			for(var i in hash)
			{
				var prefixTmp = prefix + '.' + i;

				if(_.isString(hash[i]))
				{
					str += '<input type="text" name="' + prefixTmp + '" value="' + hash[i] + '">';
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
								str += '<input type="text" name="' + p + '" value="' + hash[i][j] + '">';
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

			return str;
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

		render: function(eventName)
		{
			this.formStr = new fieldsFactory();

			this.walk(this.structure, '', '');

			var formStr  = this.formStr.get(),
				template = Handlebars.compile(tContent);
				Handlebars.registerPartial('formStr', formStr);

			var html     = template(this.model.toJSON());
			
			this.html(html, 'app-form');

			return this;
		},

		onClose: function()
		{
			//tapioca.beforeunload = false;
			//_.bindAll(this, 'render');
			//this.model.unbind('change', this.render);
			//this.model.unbind('reset', this.render);
		}
	});


	var fieldsFactory = function()
	{
		var formHtml =  '<fieldset><legend>New document</legend>',
			firstFieldset =  '</fieldset>',
			firstFieldsetClose = false,
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
				case 'media':
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

			str += '<input type="'+item.type+'" name="' + getName(item, prefix) + '" value="{{' + id + '}}">';

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
			formHtml += '<textarea class="input-xlarge" name="' + getName(item, prefix) + '" rows="3">{{' + item.id + '}}</textarea>';
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

		fields.media = function(item, prefix, key)
		{
			formHtml += '<a class="btn file-list-trigger" href="javascript:void(0)" data-prefix="' + getName(item, prefix) + '" data-key="'+key+'">\
					<i class="icon-plus"></i>\
					Link\
				</a>{{{_embedData ' + item.id + ' prefix="' + getName(item, prefix) + '"}}}';
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
			formHtml += '<input type="checkbox" value="1" name="' + getName(item, prefix) + '"{{isSelected '+ item.id + ' default="1" checked="checked"}}>';
		};

		// Getter//Setter

		return {
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

	return view;
});