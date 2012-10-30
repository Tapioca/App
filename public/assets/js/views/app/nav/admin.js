
$.Tapioca.Views.NavAdmin = Backbone.View.extend(
{
	className: 'app-nav',

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