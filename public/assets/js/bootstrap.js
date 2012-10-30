// once user loggued in, we fetch every collections
// and store all entries. They will be sync on model update

$.Tapioca.Bootstrap = function()
{
    if( $.Tapioca.Session.isAdmin() )
    {
        $.Tapioca.Apps = new $.Tapioca.Collections.Apps();

        $.Tapioca.Apps.fetch({async: false});

        $.Tapioca.Users = new $.Tapioca.Collections.Users();

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

    var userApps = new $.Tapioca.Collections.Apps();

    userApps.fetch({
        data: { 
            set: appsId 
        }, 
        success: function( collection, response)
        {
            var total = collection.length,
                inc   = 0;

            _.each( collection.models, function( app )
            {
                ++inc;

                var appslug = app.get('slug');

                $.Tapioca.UserApps[ appslug ].app = app;
                
                loadCollection( appslug );
                
                if( inc == total )
                {
                    $.Tapioca.Mediator.publish('data::loaded');
                }
            });
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
                // console.log(collection)
            },
            error: function(){}
        });
    };
};