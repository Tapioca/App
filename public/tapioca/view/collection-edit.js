define([
	'backbone',
	'text!template/content/content.html',
], function(Backbone, tContent)
{
	var view = Backbone.View.extend(
	{

		el: $('#app-content'),

		template: tContent,

		initialize: function()
		{
			this.model.bind("change", this.render, this);
		},

		render: function(eventName)
		{
			console.log(this.model.toJSON());
			//$(this.el).html(this.template(this.model.toJSON()));
			return this;
		},

		events:
		{
			"change input": "change",
			"click .save": "save",
			"click .delete": "delete"
		},

		change: function(event)
		{
			var target = event.target;
			console.log('changing ' + target.id + ' from: ' + target.defaultValue + ' to: ' + target.value);
			// You could change your model on the spot, like this:
			// var change = {};
			// change[target.name] = target.value;
			// this.model.set(change);
		},

		save: function()
		{
			this.model.set({
			name: $('#name').val(),
			desc: $('#desc').val(),
			status: $('#status').val(),
			structure: jQuery.parseJSON($('#structure').val()),
			summary: jQuery.parseJSON($('#summary').val())
			});
			if (this.model.isNew()) {
			app.TapiocaList.create(this.model);
			} else {
			this.model.save();
			}
			return false;
		},

		delete: function()
		{
			this.model.destroy({
			success: function() {
			alert('Tapioca deleted successfully');
			window.history.back();
			}
			});
			return false;
		},

		close: function()
		{
			this.$el.unbind();
			this.el.empty();
		}
	});

	return view;
});