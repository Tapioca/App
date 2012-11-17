
var formFactory = function()
{
    this.breakline          = "\n";
    this.html               = '{{#model}}' + this.breakline;
    this.firstFieldset      = '{{/model}}';
    this.firstFieldsetClose = false;
    this.wysiwygInc         = 0;
    this.inc                = 0;
    this.dependencies       = [];
}

formFactory.prototype.indent = function( _inc )
{
    if( _.isUndefined( _inc ) )
    {
        _inc = 1;
    }

    var indent = '    ',
        str    = '';

    for( var i = -1; ++i < _inc;)
    {
        str += indent;
    }

    return str;
};


formFactory.prototype.prefix = function(_prefix_tmp, _prefix, _id, _is_array)
{
    _prefix_tmp = (_prefix != '') ? _prefix + '.' + _id : _id;

    return this.setPrefix(_prefix_tmp, _is_array, _id);
};

formFactory.prototype.getHtml = function()
{
    if(!this.firstFieldsetClose)
    {
        this.firstFieldsetClose = true;
        this.html += this.firstFieldset;
    }

    var result = this.html;
    this.html = '';

    return result;
};

formFactory.prototype.getDependencies = function()
{
    var dependencies = this.dependencies;

    this.dependencies = [];

    return dependencies;
}

formFactory.prototype.setPrefix = function(_prefix, _isArray, _id)
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

formFactory.prototype.setRules = function(_item, _prefix)
{
    // Rules
    if( !_.isUndefined( _item.rules ) )
    {
        var _rules_str = _item.rules.join('|');

        return ' data-rules="' + _rules_str + '" data-label="' + _item.label + '"';
    }

    return '';
};

formFactory.prototype.getName = function(_item, _prefix)
{
    var _name = '';

    _prefix = this.setPrefix(_prefix, false, _item.id);
    
    if(_prefix != '')
    {
        _prefix = _prefix + '.';
    }
    
    _name = _prefix + _item.id;

    if( !_.isUndefined( _item.repeat ) || _item.type == 'checkbox')
    {
        _name += '[]';
    }
    
    return _name;
}

formFactory.prototype.walk = function(_structure, _prefix, _previous_key)
{
    _.each(_structure, function(item, key)
    {
        var prefix_tmp = '';
        var _key = (!_.isBlank(_previous_key)) ? _previous_key+'.'+key : key;

        // Recurssion
        if(!_.isUndefined(item.node))
        {
            // start new fieldset
            this.define('open', item, _prefix, _key);

            var isArray = (item.type == 'array');
            prefix_tmp   = this.prefix(prefix_tmp, _prefix, item.id, isArray);

            if(isArray)
            {
                this.define('incCounter', item);
            }

            if(_.isUndefined(item.template))
            {
                this.walk(item.node, prefix_tmp, _key);
            }
            else
            {
                this.define('template', item.template, null, null);
            }

            this.define('close', item, _prefix, _key);
        }
        else
        {
            this.define('row', item, _prefix, _key);
            //this.formStr.setItem(item, _prefix, _key)
        }

    }, this);
};

formFactory.prototype.define = function(type, item, prefix, key)
{
    this.html += this[type](item, prefix, key);
}

formFactory.prototype.getType = function(type)
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

formFactory.prototype.incCounter = function(item)
{
    return '{{_incCounter counter="' + item.id + '"}}'
}

formFactory.prototype.loopStart = function(item)
{
    return '{{#atLeastOnce ' + item.id + ' type="' + item.type + '"}}'
}
    
formFactory.prototype.loopEnd = function()
{
    return '<hr>{{/atLeastOnce}}';
}

formFactory.prototype.open = function(item)
{
    var str = '';

    if(!this.firstFieldsetClose)
    {
        this.firstFieldsetClose = true;
        str += this.firstFieldset;
    }

    str += '<fieldset class="subgroup">';

    if(!_.isUndefined(item.label) && !_.isBlank(item.label))
    {
        str += '<legend>'+ item.label +'</legend>';
    }

    str += this.loopStart(item);
    
    return str;
};

formFactory.prototype.close = function(item, prefix, key)
{
    var str = this.loopEnd();

    if(item.type == 'array')
    {
        this.html += '<p class="align-right">\
            <a class="btn btn-mini array-repeat-trigger" data-prefix="' + getName(item, prefix) + '" data-key="'+key+'" href="javascript:void(0);">\
                <i class="icon-plus"></i>\
                Ajouter\
            </a>\
        </p>';
    }

    str += '</fieldset>';

    return str;

};

formFactory.prototype.input = function(item, prefix, key)
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

    str += '<input type="'+item.type+'"' + this.setRules(item, prefix) + ' class="';
    str += (_.isUndefined(item.class)) ? 'span7' : item.class; 
    str += '"';
    str += (item.type =='date') ? ' data-' : ' ';
    str += 'name="' + this.getName(item, prefix) + '"';
    str += (item.type =='date') ? ' readonly="readonly"' : ' ';
    str += (item.type =='date') ? ' value="{{displayDate ' + id + ' format="DD/MM/YYYY"}}"' : '  value="{{' + id + '}}"';
    str += '>';

    if(item.type =='date')
    {
        str += '<input type="hidden" name="' + this.getName(item, prefix) + '" value="{{' + id + '}}">';
    }

    if(item.repeat)
    {
        str += '<a href="javascript:void(0)" class="btn btn-mini input-repeat-trigger" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'"><i class="icon-repeat-trigger"></i></a>';
    }

    if(item.repeat && item.pattern)
    {
        str += '</li>';
    }

    if(item.repeat && !item.pattern)
    {
        str += '{{/atLeastOnce}}</li></ul>';
    }

    return str;
};

formFactory.prototype.textarea = function(item, prefix)
{
//          if(!_.isUndefined(item.wysiwyg))
//          {
//              ++this.wysiwygInc;
//              this.html += '<div id="wysihtml5-toolbar-'+this.wysiwygInc+'" class="wysihtml5-toolbar" style="display: none;">'+"\n"+
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
//          }

    var str = '<textarea class="span7"';
    
    if(!_.isUndefined(item.wysiwyg))
    {
        str += ' data-wysiwyg="true"'
        // data-toolbar="wysihtml5-toolbar-'+this.wysiwygInc+'" id="wysihtml5-textarea-'+this.wysiwygInc+'"
    }

    str += ' name="' + this.getName(item, prefix) + '" rows="3">{{' + item.id + '}}</textarea>';

    return str;
};

formFactory.prototype.select = function(item, prefix)
{
    var klass = (_.isUndefined(item.className)) ? '': item.className;

    var str = '<select name="' + this.getName(item, prefix) + '" class="'+klass+'">' + this.breakline;

    if(!_.isUndefined(item.source))
    {
        item.options = null; //Tapp.Documents.Form.GetSource(_element.source);
    }
    
    var options = '';
    
    for (var i in item.options)
    {
        // option group
        if( _.isArray( item.options[i] ) )
        {
            options += '<optgroup label="' + i + '">' + this.breakline;
            
            for(var j = -1, nbOptions = item.options[i].length; ++j < nbOptions;)
            {
                options += '<option value="' + item.options[i][j].value +'">' + item.options[i][j].label + '</option>' + this.breakline;
            }

            options += '</optgroup>'+"\n";
        }
        else
        {
            options += this.indent(4)+'<option value="' + item.options[i].value +'"{{isSelected '+ item.id + ' default="' + item.options[i].value +'" attribute="selected"}}>' + item.options[i].label + "</option>\n";

        }
    }

    str += options;
    str += '</select>';

    return str;
};

formFactory.prototype.file = function(item, prefix, key)
{
    // NESTED HELPERS NOT ALLOWED 
    // https://github.com/wycats/handlebars.js/issues/222
    var _prefix    =  this.getName(item, prefix).replace(/\[{{/g, '_#').replace(/}}\]/g, '#_').replace(/"/g, 'II'),
        str        = '',
        dependency = {
            type:       'library'
        };

    this.dependencies.push( dependency );

    str += '{{{ _embedData ' + item.id + ' prefix="' + _prefix + '" }}}\
                <div class="btn-group float-left">\
                    <a class="btn file-list-trigger" href="javascript:void(0)" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'">\
                        <i class="icon-file"></i>\
                        library\
                    </a>';

    if( !_.isUndefined( item.upload ) )
    {
        str += '<a class="btn" href="javascript:void(0)">\
                        <i class="icon-upload"></i>\
                        upload\
                    </a>';
    }

    str += '</div>';

    return str;
};

formFactory.prototype.dbref = function(item, prefix, key)
{
    // NESTED HELPERS NOT ALLOWED 
    // https://github.com/wycats/handlebars.js/issues/222
    var _prefix    =  this.getName(item, prefix).replace(/\[{{/g, '_#').replace(/}}\]/g, '#_').replace(/"/g, 'II'),
        str        = '',
        dependency = {
            type:      'collection',
            namespace: item.collection
        };

    this.dependencies.push( dependency );

    str += '<div class="btn-group float-left">\
                    <a class="btn doc-list-trigger" href="javascript:void(0)" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'" data-collection="' + item.collection + '"';

    if(!_.isUndefined(item.embedded))
    {
        str += ' data-embedded="' + item.embedded.join('::') + '"';
    }

    str += '>\
                        <i class="icon-file"></i>\
                        Select\
                    </a>\
                </div>\
                {{{ _embedDoc ' + item.id + ' prefix="' + _prefix + '" collection="' + item.collection + '"}}}';

    return str;
};

formFactory.prototype.row = function(item, prefix, key)
{
    var type = this.getType(item.type),
        str  = '';

    str += this.indent()+'<div class="control-group">' + this.breakline;
    
    if(item.label != '')
    {
        str += this.indent(2)+'<label class="control-label">'+item.label+'</label>' + this.breakline;
    }

    str += this.indent(2)+'<div class="controls">' + this.breakline;
    str += this.indent(3)+this[type](item, prefix, key) + this.breakline;
    str += this.indent(2)+'</div>' + this.breakline + this.indent() + '</div>' + this.breakline;

    return str;
};

formFactory.prototype.bool = function(item, prefix, key)
{
    return '<input type="checkbox" value="1" name="' + getName(item, prefix) + '"{{isSelected '+ item.id + ' default="1" attribute="checked"}}>';
};

formFactory.prototype.template = function(str)
{
    this.html += str;
};

