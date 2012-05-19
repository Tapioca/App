define('template/helpers/isSelected', ['Handlebars'], function ( Handlebars )
{
	function isSelected ( value, options )
	{
		if(value == options.hash.default)
		{
			return ' '+options.hash.attribute;
		}
		return;
	}

	Handlebars.registerHelper( 'isSelected', isSelected );

	return isSelected;
});