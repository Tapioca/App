define([
	'tapioca',
	'view/document-list-item'
], function(tapioca, vDocumentListItem)
{
	return Backbone.View.extend(
	{
		mode: 'select',
		
		initialize: function(options)
		{
			this.mode = _.isUndefined(options.mode) ? this.mode : options.mode;

			this.collection.bind('reset', this.render, this);
			this.collection.bind('add', this.renderItem, this);
		},

		events:
		{
		},

		render: function()
		{
			_.each(this.collection.models, this.renderItem, this);

			return this;
		},

		renderItem: function(model)
		{
			// remove default message
			this.$el.find('tr#file-collections-empty').remove();

			this.$el.prepend(new vDocumentListItem({model: model, mode: this.mode}).render().el);
		},

		onClose: function()
		{
			this.collection.unbind('reset', this.render);
			this.collection.unbind('add', this.close);
		}
	});
});