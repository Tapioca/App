
var formFactory = function( locale )
{
    this.locale             = locale;
    this.html               = '{{#model}}';
    this.firstFieldset      = "\n" + '{{/model}}';
    this.firstFieldsetOpen  = false;    
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

    return "\n"+str;
};


formFactory.prototype.prefix = function(_prefix_tmp, _prefix, _id, _is_array)
{
    _prefix_tmp = (_prefix != '') ? _prefix + '.' + _id : _id;

    return this.setPrefix(_prefix_tmp, _is_array, _id);
};

formFactory.prototype.getHtml = function()
{
    if( !this.firstFieldsetClose )
    {
        this.firstFieldsetClose = true;
        this.html += this.indent(0) +
                     '</fielset>';
    }

    // close {{#model}}
    this.html += this.firstFieldset;
    // prevent next call to include {{/model}}
    this.firstFieldset = '';

    var result = this.html;
    this.html = '';

    return result;
};

formFactory.prototype.getDependencies = function()
{
    var dependencies = this.dependencies;

    // reset
    this.dependencies      = [];

    return dependencies;
}

formFactory.prototype.setDependencies = function( namespace )
{
    if( $.inArray( namespace, this.dependencies) === -1 )
        this.dependencies.push( namespace );
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
        if( !_.isUndefined( item.node ) )
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
    return this.indent(1) + '{{#atLeastOnce ' + item.id + ' type="' + item.type + '"}}'
}
    
formFactory.prototype.loopEnd = function()
{
    return this.indent(1) + '<hr>' + this.indent(1) + '{{/atLeastOnce}}';
}

formFactory.prototype.open = function(item)
{
    var str = '';

    if( !this.firstFieldsetClose )
    {
        this.firstFieldsetClose = true;
        str += this.indent(0) + '</fieldset>';
    }

    str += this.indent(0) + '<fieldset class="subgroup">';

    if(!_.isUndefined(item.label) && !_.isBlank(item.label))
    {
        str += this.indent() + '<legend>'+ item.label +'</legend>';
    }

    str += this.loopStart(item);
    
    return str;
};

formFactory.prototype.close = function(item, prefix, key)
{
    var str = this.loopEnd();

    if(item.type == 'array')
    {
        str +=  this.indent(1) + 
                '<p class="align-right">' +
                this.indent(2) + 
                '<a class="btn btn-mini array-repeat-trigger" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'" href="javascript:void(0);">' +
                this.indent(3) + 
                '<i class="icon-plus"></i> Ajouter' +
                this.indent(2) + 
                '</a>' +
                this.indent(1) + 
                '</p>';
    }

    str += this.indent(0) + '</fieldset>';

    return str;

};

formFactory.prototype.input = function(item, prefix, key)
{
    var str = '',
        id  = (item.repeat && !item.pattern) ? 'this' : item.id;
        ind = 3;

    if(item.repeat && !item.pattern)
    {
        str += '<ul class="input-repeat-list">' + 
                this.indent(4) + 
                '{{#atLeastOnce ' + item.id + ' type="array"}}' + 
                this.indent(4) + 
                '<li>';

        ind = 5;
    }

    if(item.repeat && item.pattern)
    {
        str += '<li>';
    }

    var type = (item.type =='date') ? 'text' : item.type
      , klass = (item.type =='date') ? 'input-date ' : ''

    klass += (_.isUndefined( item.class )) ? 'span7' : item.class; 

    str += this.indent( ind );
    str += '<input type="'+type+'"' + this.setRules(item, prefix) + ' class="';
    str += klass; 
    str += '"';
    str += (item.type =='date') ? ' data-' : ' ';
    str += 'name="' + this.getName(item, prefix) + '"';
    str += (item.type =='date') ? ' readonly="readonly"' : ' ';
    str += (item.type =='date') ? ' value="{{dateFromTimestamp ' + id + '}}"' : ' value="{{' + id + '}}"';
    str += '>';

    if(item.type =='date')
    {
        str += this.indent( ind );
        str += '<input type="hidden" name="' + this.getName(item, prefix) + '" value="{{' + id + '}}">';
    }

    if(item.repeat)
    {
        str +=  this.indent( ind ) +
                '<a href="javascript:void(0)" class="btn btn-mini input-repeat-trigger" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'">' +
                this.indent( (ind + 1) ) +
                '<i class="icon-repeat-trigger"></i>' +
                this.indent( ind ) +
                '</a>';
    }

    if(item.repeat && item.pattern)
    {
        str += '</li>';
    }

    if(item.repeat && !item.pattern)
    {

        str +=  this.indent(4) +
                '</li>' +
                this.indent(4) + 
                '{{/atLeastOnce}}'+
                this.indent(3) + 
                '</ul>';
    }

    return str;
};

formFactory.prototype.textarea = function(item, prefix)
{
    var str = this.indent(3) +'<textarea class="span7"';
    
    if(!_.isUndefined(item.wysiwyg))
    {
        str += ' data-wysiwyg="true"';

        if( _.isArray( item.wysiwyg ) )
        {
            str += ' data-toolbar="' + item.wysiwyg.join('::') + '"';
        }
    }

    str += ' name="' + this.getName(item, prefix) + '" rows="3">{{' + item.id + '}}</textarea>';

    return str;
};

formFactory.prototype.select = function(item, prefix)
{
    var klass    = (_.isUndefined(item.className)) ? '': item.className,
        multiple = (_.isUndefined(item.multiple)) ? '': 'multiple', 
        str      = this.indent(3) +'<select name="' + this.getName(item, prefix) + '" class="'+klass+'"' + multiple + '>';

    if( ! _.isUndefined( item.source ) )
    {
        item.options = this.getSource( item.source );
    }

    // str += this.options( item.id, item.options);

    for (var i in item.options)
    {
        var options = item.options[ i ];

        // option group
        if( _.isArray( options ) )
        {
            str += this.indent(4) + '<optgroup label="' + i + '">';
            
            for(var j = -1, nbOptions = options.length; ++j < nbOptions;)
            {
                str += this.indent(5) + '<option value="' + options[j].value +'"{{isSelected '+ item.id + ' default="' + options.value +'" attribute="selected"}}>' + options[j].label + '</option>';
            }

            str += this.indent(4) + '</optgroup>';
        }
        else
        {
            str += this.indent(4) + '<option value="' + options.value +'"{{isSelected '+ item.id + ' default="' + options.value +'" attribute="selected"}}>' + options.label + '</option>';
        }
    }

    str += '</select>';

    return str;
};

// return options object
formFactory.prototype.getSource = function( source )
{
    var url     = $.Tapioca.config.apiUrl + $.Tapioca.appslug + '/document/' + source.collection + '?l=' + this.locale,
        query   = {select: [ source.label, source.value ]},
        options = [{
            label:'',
            value:''
        }];

    if( !_.isUndefined( source.sortBy ))
    {
        query.sort = source.sortBy;
    }

    url = url+'&q='+JSON.stringify(query);

    hxr = $.ajax({
        url: url,
        dataType: 'json',
        async: false,
        success: function(data)
        {
            var r = data.results;
            for( var i = -1; ++i < data.total;)
            {
                // keep first select empty
                options[ (i + 1) ] = {
                    label: r[ i ][source.label], 
                    value: r[ i ][source.label]
                };
            }
        }
    });

    return options; 
}

formFactory.prototype.bool = function(item, prefix, key)
{
    return this.indent(3) +'<label class="radio inline"><input type="radio" value="1" name="' + this.getName(item, prefix) + '"{{isSelected '+ item.id + ' default="1" attribute="checked"}}> oui</label>'
     + this.indent(3) +'<label class="radio inline"><input type="radio" value="0" name="' + this.getName(item, prefix) + '"{{isSelected '+ item.id + ' default="0" attribute="checked"}}> non</label>';
};

formFactory.prototype.group = function(item, prefix, key)
{
    var str    = '',
        inline = ( !_.isUndefined( item.inline ) ) ? ' inline' : '';
    
    for (var i in item.options)
    {
        str += this.indent(3) + '<label class="' + item.type + inline + '">' +
               this.indent(4) + '<input type="' + item.type + '" name="' + this.getName(item, prefix) + '" value="' + item.options[i].value +'"{{isSelected '+ item.id + ' default="' + item.options[i].value +'" attribute="checked"}}>' + item.options[i].label +
               this.indent(3) + '</label>';
    }

    return str;
};

formFactory.prototype.file = function(item, prefix, key)
{
    // NESTED HELPERS NOT ALLOWED 
    // https://github.com/wycats/handlebars.js/issues/222
    var _prefix    =  this.getName(item, prefix).replace(/\[{{/g, '_#').replace(/}}\]/g, '#_').replace(/"/g, 'II'),
        str        = '';

    this.setDependencies( '__library__' );

    str += this.indent(2) + '{{{ _embedData ' + item.id + ' prefix="' + _prefix + '" }}}' +
           this.indent(3) + '<div class="btn-group float-left">' +
           this.indent(4) + '<a class="btn file-list-trigger" href="javascript:void(0)" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'">' +
           this.indent(5) + '<i class="icon-file"></i> library' +
           this.indent(4) + '</a>';

    if( !_.isUndefined( item.upload ) )
    {
        str += this.indent(4) + '<a class="btn btn-upload-trigger" href="javascript:void(0)" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'">' +
               this.indent(5) + '<i class="icon-upload"></i> upload' +
               this.indent(4) + '</a>';
    }

    str += this.indent(3) + '</div>';

    return str;
};

formFactory.prototype.dbref = function(item, prefix, key)
{
    // NESTED HELPERS NOT ALLOWED 
    // https://github.com/wycats/handlebars.js/issues/222
    var _prefix    =  this.getName(item, prefix).replace(/\[{{/g, '_#').replace(/}}\]/g, '#_').replace(/"/g, 'II'),
        str        = '';

    this.setDependencies( item.collection );

    str +=  this.indent(3) +
            '<div class="btn-group float-left">'+
            this.indent(4) +
            '<a class="btn doc-list-trigger" href="javascript:void(0)" data-prefix="' + this.getName(item, prefix) + '" data-key="'+key+'" data-collection="' + item.collection + '"';

    if(!_.isUndefined(item.embedded))
    {
        str += ' data-embedded="' + item.embedded.join('::') + '"';
    }

    str += '>' +
            this.indent(5) +
            '<i class="icon-file"></i> Select'+
            this.indent(4) +
            '</a>' + 
            this.indent(3) +
            '</div>' +
            this.indent(3) +
            '{{{ _embedDoc ' + item.id + ' prefix="' + _prefix + '" collection="' + item.collection + '"}}}';

    return str;
};

formFactory.prototype.row = function(item, prefix, key)
{
    var type = this.getType(item.type),
        str  = '';

    if( !this.firstFieldsetOpen )
    {
        str += this.indent(0) + '<fieldset>';
        this.firstFieldsetOpen = true;
    }

    str += this.indent()+'<div class="control-group">';
    
    if(item.label != '')
    {
        str += this.indent(2)+'<label class="control-label">'+item.label+'</label>';
    }

    str += this.indent(2)+'<div class="controls">';
    str += this[type](item, prefix, key);
    str += this.indent(2)+'</div>' 
    str += this.indent() + '</div>';

    return str;
};


formFactory.prototype.template = function(str)
{
    return this.indent(2) + str;
};

