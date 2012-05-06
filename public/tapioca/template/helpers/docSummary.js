define('template/helpers/docSummary', ['Handlebars'], function ( Handlebars )
{
	function docSummary ( context, options )
	{
		var _html = '';
		
		for(var i in context)
		{
			_html += '<td>'+context[i]+'</td>';
		}

		return _html;
	}

	Handlebars.registerHelper( 'docSummary', docSummary );

	return docSummary;
});