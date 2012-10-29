
$.Tapioca.Components.Display = {

	username: function( uid )
	{
		var user = $.Tapioca.Users.get( uid );

		return user.get('name');
	},

	role: function( slug, uid, operator )
	{
		var app  = $.Tapioca.Apps.get( slug ),
			team = app.get('team'),
			roles = $.Tapioca.config.roles,
			target,
			shooter,
			targetRole;

		_.each(team, function( member )
		{
			if( member.id == uid )
			{
				target     = _.indexOf(roles, member.role)
				targetRole = member.role;
			}

			if( member.id == operator )
			{
				shooter     = _.indexOf(roles, member.role);
			}
		})


		var html = '<div class="dropdown btn-group">';

		if( shooter > target)
		{
			html += '<span class="label">' + targetRole + '</span>';
		}
		else
		{
			html += '<a class="dropdown-toggle label" data-toggle="dropdown" href="javascript:void(0)">' + targetRole + '</a>\
						<ul class="dropdown-menu pull-right" data-type="set-status">';

			for(var i = shooter, l = roles.length; i < l; ++i)
			{
				html += '<li><a href="javascript:void(0)" data-role="' + roles[i] +'">' + roles[i] + '</a></li>';
			}
			html += '</ul>';
		}

		html += '</div>';

		return html;

	}
}


Handlebars.registerHelper( 'username', $.Tapioca.Components.Display.username );
Handlebars.registerHelper( 'roleSelector', $.Tapioca.Components.Display.role );
