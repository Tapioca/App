
$.Tapioca.Views.Login = $.Tapioca.Views.FormView.extend({

	id: 'app-content-login',
	tagName: 'div',

	initialize: function()
	{
		this.render();
	},

	events: {
        // 'keypress :input'          : 'onEnter',
		'click #login-submit' : 'submit'
	},

	render: function()
	{
		this.$el.appendTo('#tapioca-app');
		// this.$el.html('');

		this.$el.html( $.Tapioca.Tpl.login );

		return this;
	},

	onEnter: function(event)
	{
		if (event.keyCode != 13) return;
		
		this.submit(event);
	},
	
	submit: function()
	{
		// reset alert and feedback
		this.$el.find('input').removeClass('alert');
		$('#login-feedback').html('');

		var _url  = $.Tapioca.config.apiUrl+'log',
			_data = {
				email:    $('#login-email').val(), 
				password: $('#login-pass').val() 
			},
			self  = this;

		$.ajax({
			type     : 'POST',
			dataType : 'json',
			url      : _url,
			data     : _data,
			success  : function(json)
			{
				$.Tapioca.Session.set(json)
				$.Tapioca.Mediator.publish('user::loggedIn');
			},
			error    : function(xhr, ajaxOptions, thrownError)
			{
				var response = $.parseJSON(xhr.responseText);
				
				$('#login-feedback').html(response.message);

				for(var i in response.errors)
				{
					self.$el.find('input[name='+i+']').addClass('alert');
				}
			}
		});

		return this;
	}

});