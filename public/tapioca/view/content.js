define([
	'order!jquery',
	'order!nanoScroller',
	'backbone'
], function($, nanoScroller, Backbone)
{
	var ContentView = Backbone.View.extend(
	{
		id: 'app-content',
		className: 'pane nano',
		tagName: 'div',

		html: function(_html)
		{
			this.$el
				.appendTo('#app-container')
				.html(_html);
				
			this.scroller();
		},

		scroller: function()
		{
			this.$el
				.nanoScroller({
					paneClass: 'track',
					contentClass: '.pane-content'
				});
		}
	});

	return ContentView;
});