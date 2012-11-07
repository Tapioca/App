
$.Tapioca.BeforeUnload = (function()
{
	var status = {};

	var set    = function( value, token )
	{
		if(_.isUndefined( token ) )
		{
			token = _.uniqueId('beforeUnload_');
		}

		status[token] = value;
		// console.log('set token '+token+' to '+value)
		
		return token;
	};

	var get    = function(token)
	{
		return status[token];
	}

	var verify = function()
	{
		if(Object.size(status) != 0)
		{
			for(var i in status)
			{
				if(status[i])
					return true;
			}
		}

		return false;
	}

	var clean  = function()
	{
		status = {};
	} 

	return {
		set:    set,
		get:    get,
		verify: verify,
		clean:  clean
	}

})()