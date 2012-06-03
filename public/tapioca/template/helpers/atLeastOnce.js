define('template/helpers/atLeastOnce', ['Handlebars', 'underscore'], function ( Handlebars, _ )
{
	function atLeastOnce(context, options)
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

	Handlebars.registerHelper('atLeastOnce', atLeastOnce);

	return atLeastOnce;
});