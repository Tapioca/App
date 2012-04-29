define([
	'jquery', 
	'underscore', 
	'aura/facade',
	'collection/app-collections',
	'view/apps-list-item'
], function ($, _, facade, cAppCollections, vAppsListItem) 
{

	// Subscription 'modules' for our views. These take the 
	// the form facade.subscribe( subscriberName, notificationToSubscribeTo , callBack )

	// Update view with latest todo content
	// Subscribes to: newContentAvailable

	facade.subscribe('loadAppCollections', 'appsAvailable', function (slugs)
	{
		console.log(slugs)
		var appCollections = {};

		// Get Collections for each Groups
		_.each(slugs, function(slug)
		{
			var collections = new cAppCollections( slug );
			    collections.fetch({
					success: function(collection, response)
					{
						appCollections[slug] = collection;
					},
					error: function()
					{
						appCollections[slug] = null;
					}
			    });

			//self.AppsCollection[slug].fetch();
		}, this);

		console.log(appCollections);
	});

});