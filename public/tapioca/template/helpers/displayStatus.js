define('template/helpers/displayStatus', ['Handlebars'], function ( Handlebars )
{
	function displayStatus ( revisions, options )
	{
		var value    = '';
		var label    = '';
		var active   = (revisions.active - 1);
		var revision = revisions.list[active];

		switch(revision.status)
		{
			case 0: 
					value = 'label-inverse';
					label = 'supprimé';
					break;
			case 1: 
					value = 'label-info';
					label = 'broullion';
					break;
			case 100: 
					value = 'label-success';
					label = 'publié';
					break;
		}

		return '<span class="label ' + value + '">' + label + '</span>';
	}

	Handlebars.registerHelper( 'displayStatus', displayStatus );

	return displayStatus;
});