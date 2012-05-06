define([
	'view/content',
	'hbs!template/content/collection-home'
], function(vContent, tContent)
{
	var view = vContent.extend(
	{
		initialize: function(options)
		{
			this.header = options.header;
			this.collection.bind('reset', this.render, this);
		},

		render: function()
		{
			this.header.thead = [];

			for(var i in this.header.summary)
			{
				this.header.thead.push(this.header.summary[i]);
			}

			var data = {
				header: this.header,
				documents: this.collection.toJSON()
			};

			var _html = tContent(data)

			this.html(_html);
			
			return this;
		},

		onClose: function()
		{
			this.collection.unbind('fetch', this.render);
			//this.model.unbind('reset', this.render);
		}
	});

	return view;
});