$.Tapioca.Views.RevisionRow = Backbone.View.extend(
{
	tagName:   'li',
	className: 'revision',

	initialize: function( options )	
	{
		this.revision = options.revision;
		this.tpl      = options.tpl;

		this.$el.appendTo( options.$parent );

		if( this.revision.active  )
		{
			this.$el.attr('class', 'well');
			this.$el.attr('id',    'revision-active');
		}

		return this;
	},

	render: function()
	{
		this.$el.html( this.tpl( this.revision ) );

		return this;
	}
});