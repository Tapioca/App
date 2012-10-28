
$.Tapioca.I18n = (function()
{
	var get = function( _str )
	{
		var keys = _str.split('.'),
			len  = keys.length, 
			str  = $.Tapioca.I18n.Str,
			args = [].slice.call(arguments, 1);

		for (var i = -1, l = keys.length; ++i < l;)
		{
			if ( !str[ keys[i] ] )
				return _str;

			str = str[ keys[ i ] ];
		}

		if( args.length )
		{
			console.log(str)
			console.log(args)
			str = _.vsprintf( str, args );
		}

		return str;
	};

	return {
		'get': get
	}
})();

// shortcut ala gettext
// return only the string, no arguments
var __ = function( _str )
{
	return $.Tapioca.I18n.get( _str );
}

// Handlebars Helpers
Handlebars.registerHelper( 'I18n', $.Tapioca.I18n.get );