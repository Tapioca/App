
$.Tapioca.Views.EditFile = $.Tapioca.Views.FormView.extend(
{
	initialize: function( options )
	{
		this.model.bind('reset', this.render, this);
	},

	render: function()
	{
		console.log( this.model )
	},

	submit: function()
	{
		
	}
})