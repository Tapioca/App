
$.Tapioca.Mediator = (function()
{
	var channels = {}; // mediator channels

	var subscribe = function(channel, subscription)
	{
		if (!channels[channel]) channels[channel] = [];

		var token = _.uniqueId('Mediator_');;

		channels[channel].push({
			func: subscription,
			token: token
		});

		return token;
	};

	var unsubscribe = function(channel, token)
	{
		if (!channels[channel]) return;

		if(typeof token === 'undefined')
		{
			delete channels[channel];
			return;
		}

		for (var i = -1, l = channels[channel].length; ++i < l;)
		{
			if(channels[channel][i].token == token)
			{
				channels[channel].splice(i, 1);
				return;
			}
		}
	};

	var publish = function(channel)
	{
		if (!channels[channel]) return;
		var args = [].slice.call(arguments, 1);

		for (var i = -1, l = channels[channel].length; ++i < l;)
		{
			channels[channel][i].func.apply(this, args);
		}
	};

	// DEBUG
	var mediator = function()
	{
		return channels;
	};

	return {
		'publish':     publish,
		'subscribe':   subscribe,
		'unsubscribe': unsubscribe,
		'mediator':    mediator
	};

})();
