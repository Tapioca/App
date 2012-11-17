
$.Tapioca.Components.Display = {

    dateFromTimestamp: function( timestamp, options )
    {
        if( _.isUndefined( options.hash.format ) )
        {
            options.hash.format = '%d/%m/%Y';
        }

        var date = new Date( timestamp * 1000 );

        return _.strftime( date, options.hash.format );
    },

    keyValue: function( obj, options )
    {
        var buffer = '',
            key;

        for (key in obj)
        {
            if (obj.hasOwnProperty(key))
            {
                buffer += options.fn({key: key, value: obj[key]});
            }
        }
        
        return buffer;
    },

    username: function( uid )
    {
        var user = $.Tapioca.Users.get( uid );

        return user.get('name');
    },

    digest: function( digest, options )
    {
        var _html    = '',
            urlStart = '',
            urlEnd   = '';

        if( !_.isUndefined( options.hash.uri ) )
        {
            urlStart = '<a href="' + options.hash.uri + '">';
            urlEnd   = '</a>';
        }

        for(var i in digest)
        {
            _html += '<td>' + urlStart + digest[i] +  urlEnd + '</td>';
        }

        return _html;
    },

    status: function( ref, localeKey, revisions ) //revisions, appslug
    {
        var status = -2,
            revision = null;
            
        // context == form
        if( _.isUndefined( revisions.total ) )
        {
            status   = revisions.status;
            revision = revisions.revision;
        }
        else
        {
            if( !_.isUndefined( revisions.active[ localeKey ] ))
            {
                var active = (revisions.active[ localeKey ] - 1);
        
                status   = revisions.list[active].status;
                revision = revisions.list[active].revision;
            }
        }

        var value = $.Tapioca.config.status.tech[ status ];

        var html = '<div class="dropdown btn-group">\
                        ';

        if(status > -2)
        {
            html += '<a class="dropdown-toggle label ' + value.class + '" data-toggle="dropdown" href="javascript:void(0)">' + value.label + '</a>\
                        <ul class="dropdown-menu pull-right" data-type="set-status">';

            var _public = $.Tapioca.config.status.public;

            for(var i = -1, l = _public.length; ++i < l;)
            {
                html += '<li><a href="javascript:void(0)" data-status="' + _public[i].value + '">' + _public[i].label + '</a></li>';
            }
            html += '</ul>';
        }
        else
        {
            html += '<span class="label ' + value.class + '">' + value.label + '</span>';
        }

        html += '</div>';

        return html;
    },

    localeSwitcher: function( appslug, baseUri )
    {
        var html    = '',
            locales = $.Tapioca.UserApps[ appslug ].app.get('locales');

        for(var i = -1, l = locales.length; ++i < l;)
        {
            html += '<li><a href="' + baseUri + '?l=' + locales[ i ].key + '">' + locales[ i ].label + '</a></li>';
        }

        return html;
    },

    // WARNING: work only with user apps
    role: function( appslug, uid, operator )
    {

        if( uid == operator )
        {
            // TODO: user can not edit this own role
            return '<span class="label"> YOUR OWN ROLE</span>';
        }

        var app  = $.Tapioca.UserApps[ appslug ].app,
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

Handlebars.registerHelper( 'dateFromTimestamp', $.Tapioca.Components.Display.dateFromTimestamp );
Handlebars.registerHelper( 'keyValue',          $.Tapioca.Components.Display.keyValue );
Handlebars.registerHelper( 'username',          $.Tapioca.Components.Display.username );
Handlebars.registerHelper( 'displayDigest',     $.Tapioca.Components.Display.digest );
Handlebars.registerHelper( 'localeSwitcher',    $.Tapioca.Components.Display.localeSwitcher );
Handlebars.registerHelper( 'roleSelector',      $.Tapioca.Components.Display.role );
Handlebars.registerHelper( 'docStatus',         $.Tapioca.Components.Display.status );
