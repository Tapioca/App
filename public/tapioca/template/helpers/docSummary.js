define('template/helpers/docSummary', ['Handlebars'], function ( Handlebars )
{
	function docSummary ( data, ref, appslug, namespace )
	{
		var _html    = '',
			urlStart = '',
			urlEnd   = '';
			
		if(!_.isUndefined(ref) && !_.isUndefined(appslug) && !_.isUndefined(namespace))
		{
			urlStart = '<a href="/app/' + appslug + '/document/' + namespace + '/' + ref + '">';
			urlEnd   = '</a>';
		}

		for(var i in data)
		{
			_html += '<td>' + urlStart + data[i] +  urlEnd + '</td>';
		}

		return _html;
	}

	Handlebars.registerHelper( 'docSummary', docSummary );

	return docSummary;
});