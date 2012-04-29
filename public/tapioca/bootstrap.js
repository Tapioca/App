require.config({
	'paths': {
		'text': '../assets/library/require/text',
		'order': '../assets/library/require/order',
		'jquery': '../assets/library/jquery/jquery-1.7.2',
		'underscore': '../assets/library/underscore/underscore',
		'backbone': '../assets/library/backbone/backbone',
		'Mustache': '../assets/library/mustache/mustache-wrap',
		'nanoScroller': '../assets/library/nanoscroller/jquery.nanoscroller'
	}
});
 
require([
	'order!jquery',
	'order!nanoScroller', 
	'globals',
	'view/apps-list'
], function($, nanoScroller, globals, vAppCollections)
{
	$(document).ready(function()
	{
		var $sidebar = $('#apps-nav');
		var _options = {
			classPane: 'track',
			contentSelector: 'div.pane-content'
		};

		$sidebar.nanoScroller(_options);

		// Sidebar
		var $navApps      = $sidebar.find('div.app-nav');
		var $navAppActive = $sidebar.find('div.app-nav.app-nav-active');
		var $navLinks     = $sidebar.find('div.app-nav-lists a');

		$navLinks.click(function(event)
		{
			event.preventDefault();

			$navLinks.removeClass('active');
			$(this).addClass('active');
		})

		$sidebar.find('a.app-nav-header').click(function(event)
		{
			event.preventDefault();

			var $parent = $(this).parent('div.app-nav');

			if(!$parent.hasClass('app-nav-active'))
			{
				$navAppActive.find('div.app-nav-lists').slideUp(200, function()
				{
					$navApps.removeClass('app-nav-active');
					//$parent.addClass('app-nav-active');
				});
				
				$navAppActive = $parent;

				$parent.find('div.app-nav-lists').slideDown(200, function()
				{
					$parent.addClass('app-nav-active');
				});
			}
		});

		// Load Collections
		for(var i in globals.user.groups)
		{
			var slug = globals.user.groups[i].slug;

			new vAppCollections({
				el: $('#app-nav-collections-'+slug),
				appSlug: slug
			})
		}
	});
});