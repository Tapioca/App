define([
	'jquery',
	'../assets/library/wysihtml5/advanced',
	'../assets/library/wysihtml5/wysihtml5-0.3.0'
], function()
{
	var wysiwyg = function(textarea, config)
	{
		var conf = $.extend({ parserRules:  wysihtml5ParserRules }, config);

		return new wysihtml5.Editor(textarea, conf);
	}

	return wysiwyg;
});