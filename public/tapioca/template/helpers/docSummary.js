define('template/helpers/docSummary', ['Handlebars'], function ( Handlebars )
{
	function docSummary ( data, ref, appslug, namespace )
	{
		var _html = '';
		
		for(var i in data)
		{
			_html += '<td><a href="/app/' + appslug + '/document/' + namespace + '/' + ref + '">' + data[i] + '</a></td>';
		}

		return _html;
	}

	Handlebars.registerHelper( 'docSummary', docSummary );

	return docSummary;
});