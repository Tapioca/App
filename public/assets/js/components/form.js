
$.Tapioca.Components.Form = {

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

Handlebars.registerHelper( 'atLeastOnce', $.Tapioca.Components.Form.atLeastOnce );
Handlebars.registerHelper( 'isSelected',  $.Tapioca.Components.Form.isSelected );
Handlebars.registerHelper( 'isEmpty',     $.Tapioca.Components.Form.isEmpty );
Handlebars.registerHelper( 'isNotEmpty',  $.Tapioca.Components.Form.isNotEmpty );
