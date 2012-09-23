
// Usage: {{#keyValue obj}} Key: {{key}} // Value:{{value}} {{/keyValue}}
 
define('template/helpers/keyValue', ['Handlebars', 'underscore'], function ( Handlebars, _ )
{
	function keyValue(obj, fnc)
	{
		var buffer = '',
			key;

		for (key in obj)
		{
			if (obj.hasOwnProperty(key))
			{
				buffer += fnc({key: key, value: obj[key]});
			}
		}
		
		return buffer;
	}

	Handlebars.registerHelper('keyValue', keyValue);

	return keyValue;
});