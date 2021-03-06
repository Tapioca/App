
$.Tapioca.Components.Display = {

    userNameCache: [],
    appNameCache:  [],

    dateFromTimestamp: function( timestamp, options )
    {
        if( _.isUndefined( timestamp) )
            return;

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

        if ( !$.Tapioca.Components.Display.userNameCache[ uid ] )
        {
            var user = $.Tapioca.Users.get( uid );

            $.Tapioca.Components.Display.userNameCache[ uid ] = user.get('name');
        }

        return $.Tapioca.Components.Display.userNameCache[ uid ];
    },

    appname: function( uid )
    {

        if ( !$.Tapioca.Components.Display.appNameCache[ uid ] )
        {
            var app = $.Tapioca.Apps.get( uid );

            $.Tapioca.Components.Display.appNameCache[ uid ] = app.get('name');
        }

        return $.Tapioca.Components.Display.appNameCache[ uid ];
    },

    jobStatusText: function( status )
    {
        var str;

        switch( status )
        {
            case 1:  
                    str = 'status_waiting';
                    break;
            case 2:
                    str = 'status_running';
                    break;
            case 3:
                    str = 'status_failed';
                    break;
            case 4: 
                    str = 'status_complete';
                    break;
        }

        return __('jobs.' + str );
    },

    jobStatusLabel: function( status )
    {
        var str;

        switch( status )
        {
            case 1:  
                    str = 'status_waiting';
                    break;
            case 2:
                    str = 'info';
                    break;
            case 3:
                    str = 'important';
                    break;
            case 4: 
                    str = 'success';
                    break;
        }

        return 'label-' + str;
    },

    digest: function( digest, options )
    {
        var _html    = '',
            urlStart = '',
            urlEnd   = '',
            schema   = ( !_.isUndefined( options.hash.schema ) ) ? options.hash.schema : digest;

        if( !_.isUndefined( options.hash.uri ) )
        {
            urlStart = '<a href="' + options.hash.uri + '" data-bypass="true">';
            urlEnd   = '</a>';
        }
        
        for(var i in schema)
        {
            var str = ( !_.isUndefined( digest[i] )) ? urlStart + digest[i] +  urlEnd : '';
            _html += '<td>' + str + '</td>';
        }

        return _html;
    },

    status: function( revisions, localeKey  ) //revisions, appslug
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

        var value = $.Tapioca.config.status.tech[ status ],
            html  = '';

        if(status > -2)
        {
            return '<a class="dropdown-toggle label ' + value.class + '" data-toggle="dropdown" href="javascript:void(0)">' + value.label + '</a>';
        }
        else
        {
            return '<span class="label ' + value.class + '">' + value.label + '</span>';
        }
    },

    statusList: function()
    {
        var html    = '<ul class="dropdown-menu" data-type="set-status">',
            _public = $.Tapioca.config.status.public;

        for(var i = -1, l = _public.length; ++i < l;)
        {
            html += '<li><a href="javascript:void(0)" data-status="' + _public[i].value + '">' + _public[i].label + '</a></li>';
        }
        
        html += '</ul>';

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

        var app   = $.Tapioca.UserApps[ appslug ].app,
            team  = app.get('team'),
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
                        <ul class="dropdown-menu" data-type="set-status">';

            for(var i = shooter, l = roles.length; i < l; ++i)
            {
                html += '<li><a href="javascript:void(0)" data-role="' + roles[i] +'">' + roles[i] + '</a></li>';
            }
            html += '</ul>';
        }

        html += '</div>';

        return html;

    },

    fileSize: function( bytes )
    {
        if ( bytes < 1024)
        {
            return bytes;
        }

        if ( bytes < 1024 * 1024)
        {
            return Math.round(bytes/1024, 2) + ' ko';
        }

        if ( bytes < 1024 * 1024 * 1024)
        {
            return Math.round( bytes/1024/1024, 2) + ' mo';
        }

        if ($bytes < 1024 * 1024 * 1024 * 1024)
        {
            return Math.round($bytes/1024/1024/1024, 2) + ' go';
        }
    },

    imageSize: function( size )
    {
        return size.width + '*' + size.height;
    },

    printR: function( obj )
    {
        var str  = '<pre>';
            str += JSON.stringify(obj, null, "    ");
            str += '</pre>';

        return str;
    }
}

Handlebars.registerHelper( 'dateFromTimestamp', $.Tapioca.Components.Display.dateFromTimestamp );
Handlebars.registerHelper( 'keyValue',          $.Tapioca.Components.Display.keyValue );
Handlebars.registerHelper( 'username',          $.Tapioca.Components.Display.username );
Handlebars.registerHelper( 'appname',           $.Tapioca.Components.Display.appname );
Handlebars.registerHelper( 'displayDigest',     $.Tapioca.Components.Display.digest );
Handlebars.registerHelper( 'localeSwitcher',    $.Tapioca.Components.Display.localeSwitcher );
Handlebars.registerHelper( 'roleSelector',      $.Tapioca.Components.Display.role );
Handlebars.registerHelper( 'docStatus',         $.Tapioca.Components.Display.status );
Handlebars.registerHelper( 'jobStatusText',     $.Tapioca.Components.Display.jobStatusText );
Handlebars.registerHelper( 'jobStatusLabel',    $.Tapioca.Components.Display.jobStatusLabel );
Handlebars.registerHelper( 'fileSize',          $.Tapioca.Components.Display.fileSize );
Handlebars.registerHelper( 'imageSize',         $.Tapioca.Components.Display.imageSize );
Handlebars.registerHelper( 'printR',            $.Tapioca.Components.Display.printR );
