define([
	'tapioca',
	'Handlebars',
	'view/content',
	'text!template/content/document-edit.html',
	'template/helpers/isSelected',
	'template/helpers/atLeastOnce',
	'underscore.string',
	'form2js'
], function(tapioca, Handlebars, vContent, tContent, isSelected, atLeastOnce, _s, form2js)
{
	var view = vContent.extend(
	{
		template: tContent,
		formStr: null,
		inc:0,
		level: 0,

		initialize: function(options)
		{
			this.schema     = options.schema.toJSON();
			this.structure  = this.schema.structure;
			this.appSlug    = options.appSlug;

			_.bindAll(this, 'render');
			this.model.on('change', this.render);

			if(options.forceRender)
			{
				this.render();
			}
		},

		events:
		{
//			'change input': 'change',
			'click .save'  : 'save',
			'click .array-adder': 'addNode'
//			'click .delete': 'delete'
		},

		save: function()
		{
			var slug = this.appSlug;
			var formData = form2js('tapioca-document-form', '.');
			this.model.set(formData);

			if (this.model.isNew())
			{
				this.model.create(this.model);
			}
			else
			{
				this.model.save();
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

					var is_array = (item.type == 'array');
					prefix_tmp   = this.formStr.prefix(prefix_tmp, _prefix, item.id, is_array);

					this.walk(item.node, prefix_tmp, _key);

					this.formStr.define('close', item, _prefix, _key);
				}
				else
				{
					this.formStr.setItem(item, _prefix, _key)
				}

			}, this);
		},

		addNode: function(event)
		{
			var $target = $(event.target);
			var prefix  = $target.attr('data-prefix');
			var key     = $target.attr('data-key');

			console.log(this.structure[key].node);

			this.walk(this.structure[key].node, prefix, key);

			console.log(this.formStr.get())

		},

		render: function(eventName)
		{
			this.formStr = new fieldsFactory();

			this.walk(this.structure, '', '');

			var self = this;
			var template = Handlebars.compile(tContent);
			Handlebars.registerPartial('formStr', this.formStr.get());
			Handlebars.registerHelper('_incCounter', function()
			{
				++self.inc;
			});
			Handlebars.registerHelper('_getCounter', function()
			{
				return self.inc;
			});

			var html     = template(this.model.toJSON());
			
			this.html(html, 'app-form');

			return this;
		}
	});


	var fieldsFactory = function()
	{
		var formHtml =  '<fieldset style="height:auto"><legend>New document</legend>',
			firstFieldset =  '</fieldset>',
			firstFieldsetClose = false,
			inc = 0;

		// Field name Helpers

		var setPrefix = function(_prefix, _is_array)
		{
			if(_is_array && _prefix != '')
			{
				_prefix = _prefix + '[{{_getCounter}}]';
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

			_prefix = setPrefix(_prefix, false);
			
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

		// Field definition

		var fields = {};

		fields.open = function(item)
		{
			if(!firstFieldsetClose)
			{
				firstFieldsetClose = true;
				formHtml += firstFieldset;
			}

			var str = '<fieldset class="subgroup">';

			if(!_s.isBlank(item.label))
			{
				str += '<legend>'+ item.label +'</legend>';
			}

			str += '{{#atLeastOnce ' + item.id + '}}{{_incCounter}}'

			formHtml += str;
		};

		fields.close = function(item, prefix, key)
		{
			formHtml += '<hr>{{/atLeastOnce}}';

			if(item.type == 'array')
			{
				formHtml += '<p class="align-right">\
					<a class="btn btn-mini array-adder" data-prefix="' + getName(item, prefix) + '" data-key="'+key+'" href="javascript:void(0);">\
						<i class="icon-plus"></i>\
						Ajouter\
					</a>\
				</p>';
			}

			formHtml += '</fieldset>';

		};

		fields.input = function(item, prefix)
		{
			var str = '<input type="'+item.type+'" name="' + getName(item, prefix) + '" value="{{' + item.id + '}}">';

			if(item.repeat)
			{
				str += '<span class="collection-item-attributes-options-triger ui-state-default ui-corner-all float-left margin-5px" data-repeat="{{cloneId}}"><span class="ui-icon"></span></span>';
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

		fields.media = function(item, prefix)
		{
			formHtml += '';
		};

		// Getter//Setter

		return {
			'setItem': function(item, prefix, key)
			{
				// Find the right item's type
				// will be usefull for template

				switch(item.type)
				{
					case 'textarea':
					case 'select':
					case 'media':
					case 'bool':
					case 'dbref':
									type = item.type;
									break;
					case 'radio':
					case 'checkbox':
									type = 'group';
									break;

					default:
									type = 'input';
				}

				formHtml += '<div class="control-group">';
				
				if(item.label != '') 
					formHtml += '<label class="control-label">'+item.label+'</label>';

				formHtml += '<div class="controls">';
				fields[type](item, prefix, key);
				formHtml += '</div></div>';

				return;
			},
			'define': function(type, item, prefix, key)
			{
				fields[type](item, prefix, key);
			},
			'prefix': function(_prefix_tmp, _prefix, _id, _is_array)
			{
				_prefix_tmp = (_prefix != '') ? _prefix + '.' + _id : _id;
				return setPrefix(_prefix_tmp, _is_array);
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