define([
	'backbone',
	'underscore',
	'Mustache',
	'text!/tapioca/template/sidebar/apps-list.html',
], function(Backbone, _, Mustache, tAppsList)
{
	return Backbone.View.extend(
	{
		className: 'app-nav',
		initialize: function()
		{
			this.model.bind('change', this.render, this);
			this.render();
		},

		events: 
		{
			'click .app-nav-header': 'setAsActive'
		},

		setAsActive: function(event)
		{
			//event.preventDefault();
			var $target = $(event.currentTarget);
			var appId   = $target.attr('data-app-id');
			var appSlug = $target.attr('data-app-slug');
console.log(appId)
		},
 
		render: function()
		{
			//var view  = this.model.toJSON()
			var _html = Mustache.render(tAppsList, this.model.toJSON());
			
			this.$el.html(_html);
			return this;
//			console.log(this.appCollections);
/*
			//el: $('#app-nav-collections-'+this.model.get('slug'))
			var AppListView = new vAppsListItem({

				model: this.appCollections
			})
/**/
			//this.$el.append(AppsListingView.$el);
//console.log(this.appCollections);

			/*
			this.$el.html('App: ' + this.model.get('name') +
					'; Slug: ' + this.model.get('slug'));
			*/
		}
	});
});