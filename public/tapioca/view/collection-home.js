define([
	'tapioca',
	'view/content',
	'hbs!template/content/collection-home',
	'dropdown'
], function(tapioca, vContent, tContent, dropdown)
{
	var view = vContent.extend(
	{
		initialize: function(options)
		{
			this.header    = options.header;
			this.appslug   = options.appslug;
			this.namespace = options.namespace;

			//
			this.header.locales  = options.locales;
			this.header.locale   = options.locale;
			this.baseUri         = tapioca.config.base_uri+this.appslug+'/collections/'+this.namespace;

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
				appslug: this.appslug,
				namespace: this.namespace,
				editable: this.header.editable,
				baseUri: this.baseUri,
				documents: this.collection.toJSON()
			};

			var _html = tContent(data)

			this.html(_html);

			this.$el.find('.dropdown-toggle').dropdown();

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