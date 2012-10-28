$.Tapioca.Views.FormView = $.Tapioca.Views.Content.extend({

	inlineValidation: true, 
	$btnSubmit: false, 

	events: {
		'keyup :input'                : 'change',
		'change :input'               : 'change',
		'keypress :input'             : 'onEnter',
		'click button[type="submit"]' : 'submit'
	},

	change: function(event)
	{
		if( !_.isUndefined( this.model.validate ) )
		{
			var $target = $(event.target),
				name    = $target.attr('name'),
				value   = $target.val();

			// reset
			$target.removeClass('alert');

			var errorMessage = this.model.validate(name, value);

			if(!_.isBlank(errorMessage))
			{
				$target.addClass('alert');
			}
		}

		this.unLoadToken = $.Tapioca.BeforeUnload.set(true, this.unLoadToken);

		if( !this.$btnSubmit )
		{
			this.$btnSubmit = this.$el.find('button[type="submit"]');

			this.$btnSubmit.removeClass('disabled').removeAttr('disabled');
		}
		
	},

	onEnter: function(event)
	{
		if (event.keyCode != 13) return;

		// prevent bubbling
		// event.stopPropagation();
		// event.preventDefault();

		this.submit(event);
	},

	resetForm: function()
	{
		$.Tapioca.BeforeUnload.clean();

        this.$btnSubmit.button('reset');
		this.$btnSubmit.attr("disabled", "disabled").addClass('disabled');
	}

});