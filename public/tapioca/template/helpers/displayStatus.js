define('template/helpers/displayStatus', ['Handlebars', 'tapioca'], function ( Handlebars, tapioca )
{
	function displayStatus ( ref, revisions, slug, namespace ) //revisions, slug
	{
		var status = -2,
			workingLocale = tapioca.apps[slug].locale.working.key,
			revison = null;
			
		// context == form
		if(_.isUndefined(revisions.total))
		{
			status   = revisions.status;
			revision = revisions.revision;
		}
		else
		{
			if(!_.isUndefined(revisions.active[workingLocale]))
			{
				var active = (revisions.active[workingLocale] - 1);
		
				status   = revisions.list[active].status;
				revision = revisions.list[active].revision;
			}
		}

		var value = tapioca.config.status.tech[status];

		var html = '<div class="dropdown btn-group">\
						';

		if(status > -2)
		{
			html += '<a class="dropdown-toggle label ' + value.class + '" data-toggle="dropdown" href="javascript:void(0)">' + value.label + '</a>\
						<ul class="dropdown-menu pull-right" data-type="set-status">';

			for(var i = -1, l = tapioca.config.status.public.length; ++i < l;)
			{
				html += '<li><a href="javascript:void(0)" data-document="' + slug + '.' + namespace + '.' + ref + '.' + workingLocale + '.' + revision + '.' + tapioca.config.status.public[i].value+'" data-label="'+tapioca.config.status.public[i].label+'" data-class="'+tapioca.config.status.public[i].class+'">'+tapioca.config.status.public[i].label+'</a></li>';
			}
			html += '</ul>';
		}
		else
		{
			html += '<span class="label ' + value.class + '">' + value.label + '</span>';
		}

		html += '</div>';

		return html;
	}

	Handlebars.registerHelper( 'displayStatus', displayStatus );

	return displayStatus;
});