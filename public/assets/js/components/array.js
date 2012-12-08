
$.Tapioca.Components.Array = 
{
    get: function(obj, str)
    {
        str = str.split('.');
        for (var i = 0; i < str.length; i++)
            obj = obj[str[i]];
        return obj;
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
    }
};

Handlebars.registerHelper( 'arrGet',      $.Tapioca.Components.Array.get );
Handlebars.registerHelper( 'atLeastOnce', $.Tapioca.Components.Array.atLeastOnce );