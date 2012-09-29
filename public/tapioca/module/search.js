define([
	'tapioca',
	'aura/mediator',
	'underscore.string'
], function(tapioca, mediator, _s)
{
	var formatSearch = function(event)
	{
		if (event.keyCode != 13) return;

		// prevent bubbling
		event.stopPropagation();
		event.preventDefault();

		var value = $(this).val();

		if(_s.isBlank( value ))
		{
			mediator.publish('search::clear');
		}
		else
		{
			var pattern = new RegExp( $.trim( $(this).val() ).replace( / /gi, '|' ), "i");

			mediator.publish('search::send', pattern);
		}
	};

	mediator.subscribe('search::enable', function()
	{
		$('#search-query')
			.val('')
			.removeAttr('disabled')
			.removeClass('disabled')
			.keypress(formatSearch);
	});

	mediator.subscribe('search::disabled', function()
	{
		$('#search-query')
			.val('')
			.attr('disabled', 'disabled')
			.addClass('disabled')
			.unbind('keypress', formatSearch)
	});

	// Required, return the module for AMD compliance
	return true;

});