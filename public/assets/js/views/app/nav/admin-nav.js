
$.Tapioca.Views.AdminNav = Backbone.View.extend(
{
	className: 'app-nav app-nav-active',

	initialize: function()
	{
		this.$el.appendTo('#apps-nav div.pane-content');

		this.render();
	},

	render: function()
	{
		this.$el.html( $.Tapioca.Tpl.app.nav.admin );

		return this;
	}

});