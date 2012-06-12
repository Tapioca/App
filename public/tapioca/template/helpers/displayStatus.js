define('template/helpers/displayStatus', ['Handlebars', 'tapioca'], function ( Handlebars, tapioca )
{
	function displayStatus ( revisions, slug )
	{
		var value = '',
			label,
			status = -1,
			workingLocale = tapioca.apps[slug].locale.working.key;

			if(!_.isUndefined(revisions.active[workingLocale]))
			{
				var active = (revisions.active[workingLocale] - 1);
		
				status = revisions.list[active].status;
			}

		switch(status)
		{
			case -1: 
					label = 'pas rédigé';
					break;
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