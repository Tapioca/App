// once user loggued in, we fetch every collections
// and store all entries. They will be sync on model update

$.Tapioca.Bootstrap = function()
{
    $.Tapioca.Apps  = new $.Tapioca.Collections.Apps();
    $.Tapioca.Users = new $.Tapioca.Collections.Users();

    var isAdmin = $.Tapioca.Session.isAdmin();

    if( isAdmin )
    {
        $.Tapioca.Apps.fetch({async: false});

        $.Tapioca.Users.fetch({async: false});
    }

    var apps   = $.Tapioca.Session.get('apps'),
        appsId = [];

    for( var i = -1, l = apps.length; ++i < l; )
    {
        appsId.push( apps[i].slug );

        $.Tapioca.UserApps[ apps[i].slug ] = {
            collections: null,
            abstracts: null,
            app: null
        }
    }

    appsId = appsId.join(';')

    var userApps = new $.Tapioca.Collections.Apps(),
        loaded   = 0,
        users    = [],
        total;

    userApps.fetch({
        data: { 
            set: appsId 
        }, 
        success: function( collection, response)
        {
            total = collection.length;

            _.each( collection.models, function( app )
            {
                var appslug = app.get('slug'),
                    appTeam = app.get('team');

                users = _.union(users, appTeam);

                $.Tapioca.UserApps[ appslug ].app     = app;
                // $.Tapioca.UserApps[ appslug ].users   = new $.Tapioca.Collections.Users( appTeam );
                $.Tapioca.UserApps[ appslug ].library = new $.Tapioca.Collections.Files( {
                                                            appslug: appslug
                                                        });
                $.Tapioca.UserApps[ appslug ].search  = new $.Tapioca.Collections.Search( {
                                                            appslug: appslug
                                                        });
                
                $.Tapioca.UserApps[ appslug ].search.fetch();
                
                loadCollection( appslug );
            });

            if( total == 1 )
            {
                $.Tapioca.appslug = collection.at(0).get('slug');
                $.Tapioca.Mediator.publish('search::active');
            }

            // load apps's users
            if( !isAdmin )
            {
                $.Tapioca.Users.add( users ).fetch();
            }
        },
        error: function( collection, response )
        {
            // TODO: display error
            console.log( response.message );
        }
    });

    var loadCollection = function( appslug )
    {
        $.Tapioca.UserApps[ appslug ].collections = new $.Tapioca.Collections.Collections({
            appslug: appslug
        });

        $.Tapioca.UserApps[ appslug ].collections.fetch({
            success: function( collection, response )
            {
                $.Tapioca.UserApps[ appslug ].data = {};

                _.each( collection.models, function( model )
                {
                    var _namespace = model.get('namespace'),
                        _abstracts = new $.Tapioca.Collections.Abstracts({
                            appslug:   appslug,
                            namespace: _namespace
                        });

                    $.Tapioca.UserApps[ appslug ].data[ _namespace ] = {
                        schema:    false, // useless ?
                        abstracts: _abstracts
                    };
                }, this)

                ++loaded;
                
                if( loaded == total )
                {
                    $.Tapioca.Mediator.publish('data::loaded');
                }
            },
            error: function(){}
        });

        // fulltext search index
        // var apiSearch = $.Tapioca.config.apiUrl + appslug +'/search';

        // $.getJSON( apiSearch, function ( indexes )
        // {
        //     $.Tapioca.UserApps[ appslug ].searchValues = indexes;

        //     console.time('load '+appslug+' search index')

        //     indexes.forEach(function ( raw )
        //     {
        //         $.Tapioca.UserApps[ appslug ].searchIndex.add({
        //             id:    raw._ref,
        //             title: raw.title,
        //             body:  raw.body
        //         })
        //     })
        //     console.timeEnd('load '+appslug+' search index')
        // });
    };
};