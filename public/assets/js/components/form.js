
$.Tapioca.Components.Form = {

    slugify: function(_str)
    {
        var _replace = new Array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Þ', 'Ç', 'Ć', 'Č', 'Đ', 'È', 'É', 'Ê', 'Ë', 'Ğ', 'Ì', 'Í', 'Î', 'Ï', 'İ', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ß', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'þ', 'ç', 'ć', 'č', 'đ', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'Ŕ', 'ŕ', 'Š', 'Ş', 'š', 'ù', 'ú', 'û', 'ü', 'ý', 'ý', 'ÿ', 'Ž', 'ž');

        var _by = new Array('A', 'A', 'A', 'A', 'A', 'A', 'A', 'B', 'C', 'C', 'C', 'Dj', 'E', 'E', 'E', 'E', 'G', 'I', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'Ss', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'b', 'c', 'c', 'c', 'dj', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'R', 'r', 'S', 'S', 's', 'u', 'u', 'u', 'ue', 'y', 'y', 'y', 'Z', 'z');

        for(var _i = 0; _i< _replace.length; ++_i)
        {  
          _str = _str.replace(_replace[_i], _by[_i]);
        }

        // Clean up the string
        _str = _str
                .toLowerCase()                      // change everything to lowercase
                .replace(/[^-a-zA-Z0-9_\s]+/ig, '') // remove all non-alphanumeric characters except the underscore and space
                .replace(/\s/gi, '-')               // remplace space by hyphens
                .replace(/^-+|-+$/g, '')            // trim leading and trailing hyphens

        return _str;
    },

    fieldWidth: function(_parentNode, _str)
    {
        var $text  = $('<span>')
                       .html( _str )
                       .appendTo( _parentNode );

        var width  = $text.innerWidth();

        $text.remove();

        return width;
    },

    atLeastOnce: function(context, options)
    {
        var ret = '';
        
        if(_.isEmpty(context))
        {
            context = (options.hash.type == 'array') ? [''] : {};
        }

        if(_.isArray(context))
        {
            var nb = context.length;
            if(nb == 0)
                nb = 1;


            for(var i = -1; ++i < nb;)
            {
                ret = ret + options.fn(context[i]);
            }
        }
        else if(_.isObject(context))
        {
            ret = ret + options.fn(context);
        }

        return ret;
    },

    isSelected: function( value, options )
    {
        if(value == options.hash.default)
        {
            return ' '+options.hash.attribute;
        }
        return;
    },

    isEmpty: function( value)
    {
        return _.isEmpty(value);
    },

    isNotEmpty: function( value, options )
    {
        if(!_.isEmpty(value))
        {
            return options.hash.echo;
        }
    }
}
Handlebars.registerHelper( 'slugify',     $.Tapioca.Components.Form.slugify );
Handlebars.registerHelper( 'atLeastOnce', $.Tapioca.Components.Form.atLeastOnce );
Handlebars.registerHelper( 'isSelected',  $.Tapioca.Components.Form.isSelected );
Handlebars.registerHelper( 'isEmpty',     $.Tapioca.Components.Form.isEmpty );
Handlebars.registerHelper( 'isNotEmpty',  $.Tapioca.Components.Form.isNotEmpty );
