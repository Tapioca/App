define('template/helpers/localeSwitcher', ['Handlebars'], function ( Handlebars )
{
	function localeSwitcher (list, baseUri)
	{
		var str = '';
		for(var i in list)
		{
			str += '<li><a href="' + baseUri + '?locale=' + i + '">' + list[i] + '</a></li>';
		}

		return str;
	}

	Handlebars.registerHelper( 'localeSwitcher', localeSwitcher );

	return localeSwitcher;
});